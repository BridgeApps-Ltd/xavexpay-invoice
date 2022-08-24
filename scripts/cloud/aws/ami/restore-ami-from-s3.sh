echo "...  restoring ami from S3 Bucket" 
/usr/local/bin/aws ec2 create-restore-image-task \
    --object-key ami-00a3448b2e67fade8.bin \
    --bucket cargofl-ami \
    --name "CargoFL AMI"	
echo "... Completed" 
	