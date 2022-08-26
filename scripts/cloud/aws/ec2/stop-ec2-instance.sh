#----------------------Note this instance id is for example-----------------------#
#---Note you must have awscli installed and proper AMI policy to Stop instance---#
instance_id="i-0123456789abcdef"
instance_name=$(aws ec2 describe-tags --filters Name=resource-id,Values=${instance_id} Name=key,Values=Name --query Tags[].Value --output text)
echo "Stopping ec2 Instance ${instance_name}"
aws ec2 stop-instances --region ap-south-1 --instance-ids ${instance_id}
