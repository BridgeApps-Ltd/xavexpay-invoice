#----------------------Note this instance id is for example-----------------------#
#---Note you must have awscli installed and proper AMI policy to Stop instance---#

green=`tput setaf 2`
white=`tput setaf 7`
aws_region="ap-south-1"
aws_instance_id="i-0123456789abcdef"
aws_instance_name=$(aws ec2 describe-tags --filters Name=resource-id,Values=${aws_instance_id} Name=key,Values=Name --query Tags[].Value --output text)

echo "Instance Name => ${green}${aws_instance_name}${white}"
aws ec2 stop-instances --region ${aws_region} --instance-ids ${aws_instance_id} --output table
