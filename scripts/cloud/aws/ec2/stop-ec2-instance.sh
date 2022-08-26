#----------------------Note this instance id is for example-----------------------#
#---Note you must have awscli installed and proper AMI policy to Stop instance---#
instance_id="i-0123456789abcdef"
echo "Stopping ec2 Instance"
aws ec2 stop-instances --region ap-south-1 --instance-ids ${instance_id}
