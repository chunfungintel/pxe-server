#!ipxe

#set server_ip 10.226.76.19
#set nfs_path /srv/Ubuntu20-Server
#set nfs_path /srv/Ubuntu20
#kernel nfs://${server_ip}${nfs_path}/casper/vmlinuz || read void
#initrd nfs://${server_ip}${nfs_path}/casper/initrd || read void
#initrd nfs://${server_ip}/srv/Customized/initrd || read void

set http_server http://10.226.76.19:5050
kernel ${http_server}/casper/vmlinuz || read void
initrd ${http_server}/casper/initrd || read void


set network_adapter enx000ec630698d

# testing initrd customization - able to work after customized correctly
imgargs vmlinuz initrd=initrd ramdisk_size=1500000 rootfstype=ramfs ip=:::::${enx000ec630698d}:dhcp url=${http_server}/ubuntu-20.04.3-desktop-amd64.iso automatic-ubiquity url=${http_server}/preseed/example.seed auto quite || read void

#imgargs vmlinuz initrd=initrd ramdisk_size=1500000 root=/dev/ram0 rootfstype=ramfs ip=dhcp url=http://10.226.76.19:5050/ubuntu-20.04.3-desktop-amd64.iso automatic-ubiquity url=http://10.226.76.19:5050/preseed/example.seed auto quite || read void



# working ubuntu desktop
#imgargs vmlinuz initrd=initrd ramdisk_size=5000000 ip=dhcp url=http://10.226.76.19:5050/ubuntu-20.04.3-desktop-amd64.iso automatic-ubiquity url=http://10.226.76.19:5050/preseed/example.seed auto quite || read void

# working ubuntu server
#imgargs vmlinuz initrd=initrd  root=/dev/ram0 ramdisk_size=1500000 ip=dhcp url=http://10.226.76.19:5050/ubuntu-20.04.3-live-server-amd64.iso autoinstall ds=nocloud-net;s=http://10.226.76.19:5050/preseed/cloud-init/ || read void

# trying ubuntu server setup on ubuntu desktop
#imgargs vmlinuz initrd=initrd  root=/dev/ram0 ramdisk_size=1500000 ip=dhcp url=http://10.226.76.19:5050/ubuntu-20.04.3-desktop-amd64.iso autoinstall ds=nocloud-net;s=http://10.226.76.19:5050/preseed/cloud-init/ || read void


# ubuntu desktop load only
#imgargs vmlinuz initrd=initrd ramdisk_size=5000000 ip=dhcp url=http://10.226.76.19:5050/ubuntu-20.04.3-desktop-amd64.iso automatic-ubiquity quite || read void

boot || read void
