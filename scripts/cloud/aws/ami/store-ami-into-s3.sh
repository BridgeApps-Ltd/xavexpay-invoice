echo "... Initiating storing ami into S3 Bucket" 

ami_id="ami-00a3448b2e67fade8"
s3_bucket_name="cargofl-ami"

/usr/local/bin/aws ec2 create-store-image-task --image-id ${ami_id} --bucket ${s3_bucket_name}
echo "... Completed storing ami"
	
