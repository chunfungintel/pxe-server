---
title: iPXE
created: '2021-09-22T10:36:41.557Z'
modified: '2021-09-22T14:01:50.051Z'
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



