echo "...  restoring ami from S3 Bucket" 
s3_bucket_name="cargofl-ami"
s3_object_name="ami-00a3448b2e67fade8.bin"
ami_name="CargoFL AMI"

/usr/local/bin/aws ec2 create-restore-image-task --object-key ${s3_object_name} --bucket ${s3_bucket_name} --name ${ami_name}	
echo "... Completed" 
	
