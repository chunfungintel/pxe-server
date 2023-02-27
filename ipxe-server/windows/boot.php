#!ipxe



set file_server_ip 10.226.76.19
set file_nfs_path /srv/Windows-install
set rootfs_file nfs://${file_server_ip}${file_nfs_path}

kernel ${rootfs_file}/wimboot
# Must run script to start setup.exe, else unable to find boot media
initrd ${rootfs_file}/install.cmd              install.cmd
initrd ${rootfs_file}/winpeshl.ini             winpeshl.ini

set server_ip 10.226.76.19
set nfs_path /srv/Windows
set rootfs nfs://${server_ip}${nfs_path}

initrd ${rootfs}/boot/bcd         BCD
initrd ${rootfs}/boot/boot.sdi    boot.sdi
initrd ${rootfs}/sources/boot.wim boot.wim
#initrd http://10.226.76.19:8000/windows/sources/boot.wim boot.wim

initrd ${rootfs}/boot/fonts/segmono_boot.ttf segmono_boot.ttf
initrd ${rootfs}/boot/fonts/segoe_slboot.ttf segoe_slboot.ttf
initrd ${rootfs}/boot/fonts/segoen_slboot.ttf segoen_slboot.ttf
initrd ${rootfs}/boot/fonts/wgl4_boot.ttf wgl4_boot.ttf

initrd ${rootfs}/efi/boot/bootx64.efi bootx64.efi
initrd ${rootfs}/bootmgr bootmgr

boot
