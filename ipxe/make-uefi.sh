#!/bin/bash


#echo "To use NFS, need to add \"#define DOWNLOAD_PROTO_NFS\" in config/general.h"

cp general.h ./ipxe/src/config/general.h
pushd ipxe/src

cat << EOF > demo.ipxe
#!ipxe

#https://ipxe.org/gsoc/nfs

//prompt --key 0x02 --timeout 5000 Press Ctrl-B for the iPXE command line... || goto no_shell
prompt --key 0x197e --timeout 5000 Press F12 for the iPXE command line... || goto no_shell
shell
exit

:no_shell
dhcp
set dns 10.248.2.1
show uuid
#chain http://10.226.76.241:8080/ipxe?uuid=${uuid}
#chain http://10.226.76.19:8000/boot.php
chain http://inats.intel.com/ipxe?uuid=${uuid}

EOF

make bin-x86_64-efi/ecm--ncm--xhci--ehci--uhci--axge--ipxe.efi EMBED=demo.ipxe

popd
