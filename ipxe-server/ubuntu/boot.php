#!ipxe

set server_ip 10.226.76.19
set nfs_path /Ubuntu
kernel nfs://${server_ip}${nfs_path}/casper/vmlinuz || read void
initrd nfs://${server_ip}${nfs_path}/casper/initrd || read void
imgargs vmlinuz initrd=initrd root=/dev/nfs boot=casper netboot=nfs nfsroot=${server_ip}:${nfs_path} ip=dhcp splash quiet -- || read void
boot || read void
