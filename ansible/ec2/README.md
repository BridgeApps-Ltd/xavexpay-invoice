Install ansible
--------------------------------------------
sudo apt-add-repository ppa:ansible/ansible

sudo apt update

sudo apt install ansible -y

Install Aws-cli
--------------------------------------------
sudo apt install awscli -y

Install boto and boto3
--------------------------------------------
sudo apt install python3-pip

pip3 install boto boto3

run playbook
--------------------------------------------
######################################
# starting ec2 instances
######################################

ansible-playbook startAllEc2instances.yml --extra-vars "region=ap-south-1"

--------------------------------------------
######################################
# stopping ec2 instances
######################################

ansible-playbook stopAllEc2instances.yml --extra-vars "region=ap-south-1"

Site to check yaml validations 
--------------------------------------------
https://yamlchecker.com/

References
--------------------------------------------
https://docs.ansible.com/ansible/2.3/ec2_module.html#examples
