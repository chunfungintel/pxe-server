#!ipxe

set http_server http://10.226.76.19:5050
kernel ${http_server}/casper/vmlinuz || read void
initrd ${http_server}/casper/initrd || read void

set network_adapter enx000ec630698d
set preseed_adl adlps.seed

# testing initrd customization - able to work after customized correctly
imgargs vmlinuz initrd=initrd ramdisk_size=1500000 rootfstype=ramfs ip=:::::${enx000ec630698d}:dhcp url=${http_server}/ubuntu-20.04.3-desktop-amd64.iso automatic-ubiquity url=${http_server}/preseed/example.seed auto quite || read void

boot || read void
