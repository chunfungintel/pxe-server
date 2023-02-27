---
title: iPXE
created: '2021-09-22T10:36:41.557Z'
modified: '2021-09-22T14:12:26.697Z'
---

# iPXE

## Overview
To create a iPXE network boot, you will need:
* Bootable USB with ipxe EFI
* http server for hosting ipxe boot commands
* NFS server for Ubuntu boot or Samba server for Windows boot.


## EFI compile
1. Preparation
    ```
    sudo apt install -y liblzma-dev git
    ```
    More detailed packages: https://ipxe.org/download
1. Cloning
    ```
    git clone git://git.ipxe.org/ipxe.git
    ```
1. Turn on NFS support. In ipxe/src/config/general.h, add this line before #endif. Reference: https://ipxe.org/gsoc/nfs
   ```
   // Add this line
   #define DOWNLOAD_PROTO_NFS

   #endif /* CONFIG_GENERAL_H */
   ```
1. Create a auto run script when ipxe starts under ipxe/src with name "demo.ipxe". The command "chain" in "demo.ipxe" will look for file "ipxe" in the http server and continue to boot.
    ```
    #!ipxe

    prompt --key 0x197e --timeout 5000 Press F12 for the iPXE command line... || goto no_shell
    shell
    exit

    :no_shell
    dhcp
    show uuid
    set dns 10.248.2.1

    chain http://10.226.76.241:8080/ipxe?uuid=${uuid}
    #chain http://inats.intel.com/ipxe?uuid=${uuid}
    ```
1. Begin EFI compilation with USB ethernet support. Additional build target can refer to here: https://ipxe.org/appnote/buildtargets
    ```
    cd ipxe/src
    make bin-x86_64-efi/ecm--ncm--ipxe.efi EMBED=demo.ipxe
    ```

## Bootable USB Creation
**` WARNING: The target drive will be erased `**
1. Assuming USB drive at /dev/sdc, new GPT and ESP partition will be created.
    ```
    export USB_DEVICE=/dev/sdc
    sudo parted -s ${USB_DEVICE} unit MiB mklabel gpt mkpart primary 1MiB 500MiB -- set 1 esp on
    sudo mkfs.fat -F32 ${USB_DEVICE}1
    ```
1. Mount and copy ipxe's EFI into USB's partition.
    ```
    # Mount USB drive partition 1
    EFI_MOUNT=/tmp/efidrive
    EFI_DRIVE=${USB_DEVICE}1
    sudo mkfs.fat -F32 $EFI_DRIVE
    sudo mkdir -p $EFI_MOUNT
    sudo mount $EFI_DRIVE $EFI_MOUNT

    sudo mkdir -p $EFI_MOUNT/efi/boot
    sudo cp ipxe/src/bin-x86_64-efi/ecm--ncm--ipxe.efi $EFI_MOUNT/efi/boot/bootx64.efi
    sudo umount $EFI_MOUNT
    ```
## Bootable ISO Creation and mount via KVM Virtual Media support for boot
1. Create disk image and mount as loop device. Use 'losetup -a' to check loop device # disk image is mounted to. In this exmaple. it is mounted automatically to loop3.
    ```
    sudo mount -t tmpfs -o size=200M tmpfs temp/
    dd if=/dev/zero of=disk.img bs=1M count=199
    sudo losetup -fP  disk.img

    losetup -a
    ```
2. Create new partition in loop device and format it to FAT32.
    ```
    sudo fdisk /dev/loop3
    Welcome to fdisk (util-linux 2.31.1).
    Changes will remain in memory only, until you decide to write them.
    Be careful before using the write command.


    Command (m for help): n
    Partition number (1-128, default 1): 1
    First sector (34-407518, default 2048):
    Last sector, +sectors or +size{K,M,G,T,P} (2048-407518, default 407518):

    Created a new partition 1 of type 'Linux filesystem' and of size 198 MiB.

    Command (m for help): w
    The partition table has been altered.
    Calling ioctl() to re-read partition table.
    Re-reading the partition table failed.: Invalid argument

    The kernel still uses the old table. The new table will be used at the next reboot or after you run partprobe(8) or kpartx(8).

    sudo mkfs.fat -F32 /dev/loop3p1
    ```
3. Mount and copy ipxe's EFI into loop device partition.
    ```
    sudo mkfs.fat -F32 /dev/loop3p1
    sudo mount /dev/loop3p1 /mnt/part1/
    sudo mkdir -p /mnt/part1/efi/boot
    sudo cp ipxe/src/bin-x86_64-efi/ecm--ncm--ipxe.efi /mnt/part1/efi/boot/bootx64.efi
    ```
4. Export disk image as iso.
    ```
    sync
    sudo umount /mnt/part1
    sudo dd if=/dev/loop3 of=usbdiskimage.iso
    sudo losetup -d /dev/loop3
    ```
5. Set up a samba server shared folder and copy bootable iso created to it.
    ```
    sudo apt install samba
    mkdir /home/<username>/sambashare/
    sudo vi /etc/samba/smb.conf
    ```
    At the bottom of the file, add the following lines:
    ```
    # My share
    [MyShare]
      comment = Conga03 samba share
      path = /mnt/sambashare
      read only = no
      guest ok = yes
      browsable = yes
      writable = yes
      create mask = 0755
    ```
    Copy bootable iso created to samba share folder.
    ```
    cp usbdiskimage.iso /home/<username>/sambashare/
    ```

6.  In SpiderDuo KVM settings, go to Interfaces-Virtual Media->Image on Windows share and configure your samba server's IP, sharename (eg. MyShare above) and bootable iso filename.
The bootable iso will be emulated as a "UEFI Peppercon AG MultiDevice xxxxx" in boot menu.


## HTTP Server
After ipxe boot with demo.ipxe script, it will make a rest api call to the http server to get file name **ipxe** for next boot. Example:
> chain http://10.226.76.241:8080/ipxe?uuid=${uuid}

Sample ipxe file for Ubuntu boot, note that the Ubuntu will boot through NFS:
```
#!ipxe

set server_ip 10.226.76.19
set nfs_path /src/Ubuntu20

kernel nfs://${server_ip}${nfs_path}/casper/vmlinuz || read void
initrd nfs://${server_ip}${nfs_path}/casper/initrd || read void
imgargs vmlinuz initrd=initrd root=/dev/nfs boot=casper netboot=nfs nfsroot=${server_ip}:${nfs_path} ip=dhcp splash quiet -- || read void
boot || read void
```

## NFS Server for Ubuntu
The Ubuntu ISO need to be shared with NFS. First the ISO need to be mounted then shared with NFS by adding entry in /etc/exports
```
sudo mount ubuntu-21.04-desktop-amd64.iso /srv/Ubuntu21
```
```
cat /etc/exports
/srv/Ubuntu18    *(ro,sync,no_wdelay,insecure_locks,no_root_squash,insecure)
/srv/Ubuntu20     *(ro,sync,no_wdelay,insecure_locks,no_root_squash,insecure)
/srv/Windows     *(ro,sync,no_wdelay,insecure_locks,no_root_squash,insecure)
/srv/Windows-install     *(ro,sync,no_wdelay,insecure_locks,no_root_squash,insecure)
```



