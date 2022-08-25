#----------------------Note this instance id is for example-----------------------#
#---Note you must have awscli installed and proper AMI policy to Start instance---#

echo "Starting ec2 Instance"
aws ec2 start-instances --region ap-south-1 --instance-ids i-0123456789abcdef
