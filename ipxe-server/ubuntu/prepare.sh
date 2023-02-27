#!/bin/bash

export SERVER_ADDRESS=`hostname -I | awk '{print $1}'`
export NFS_FOLDER="/Ubuntu"
export ISO_IMAGE="ubuntu-18.04.5-desktop-amd64.iso"

sudo mkdir -p ${NFS_FOLDER}
sudo mount ${ISO_IMAGE} ${NFS_FOLDER}

sudo tee -a /etc/exports << EOF
${NFS_FOLDER}     *(ro,sync,no_wdelay,insecure_locks,no_root_squash,insecure)
EOF

cat << EOF > boot.php
#!ipxe

set server_ip ${SERVER_ADDRESS}
set nfs_path ${NFS_FOLDER}
kernel nfs://\${server_ip}\${nfs_path}/casper/vmlinuz || read void
initrd nfs://\${server_ip}\${nfs_path}/casper/initrd || read void
imgargs vmlinuz initrd=initrd root=/dev/nfs boot=casper netboot=nfs nfsroot=\${server_ip}:\${nfs_path} ip=dhcp splash quiet -- || read void
boot || read void
EOF
