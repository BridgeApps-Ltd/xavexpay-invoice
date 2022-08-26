Install ansible
--------------------------------------------
sudo apt-add-repository ppa:ansible/ansible

sudo apt update

sudo apt install ansible

Install Aws-cli
--------------------------------------------
sudo apt install awscli

Install boto and boto3
--------------------------------------------
sudo apt install python-pip

pip install boto boto3

run playbook
--------------------------------------------
######################################
# starting ec2 instance
######################################

ansible-playbook startEc2Instance.yml

--------------------------------------------
######################################
# stopping ec2 instance 
######################################

ansible-playbook stopEc2Instance.yml

