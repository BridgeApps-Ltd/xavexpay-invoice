
1. Create a .keys sub-directory and add private_key in .keys sub-directory

2. Use "terraform init" command to initialize terraform modules and providers

3. To check everything is working properly use terraform plan with passing below variables

terraform plan -var "aws_access_key=add_aws_access_key_here" \
               -var "aws_secret_key=add_aws_secret_key_here" \
							 -var "aws_region=ap-south-1" \
							 -var "gitpass=add_git_token_here" \
							 -var "keyPath=.keys/add_private_key_here"
							 
------------------------------------------------------------------------------------------
terraform apply -var "aws_access_key=add_aws_access_key_here" \
                -var "aws_secret_key=add_aws_secret_key_here" \
							  -var "aws_region=ap-south-1" \
							  -var "gitpass=add_git_token_here" \
							  -var "keyPath=.keys/add_private_key_here"
                
-------------------------------------------------------------------------------------------                
terraform destroy -var "aws_access_key=add_aws_access_key_here" \
                  -var "aws_secret_key=add_aws_secret_key_here" \
							    -var "aws_region=ap-south-1" \
							    -var "gitpass=add_git_token_here" \
							    -var "keyPath=.keys/add_private_key_here"
							    
