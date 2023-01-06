AWS INSPECTOR
--------------

This is a platform automation tool which gives the view of AWS Services under an account.
There are two views - 
- Table View 
- Tree View

Use each of them as needed. 

Tree view gives a dump of the entire AWS content, on selection of a EC2 or RDS node. 



Installation 
------------
1. Standard python Installation,
2. install dependencies > requirements.txt
3. create a user to launch this service
4. Install AWS CLI 
5. setup AWS profiles with different names as needed to connect to the respective accounts, for e.g. 

aws configure --profile cargofl_test
aws configure --profile cargofl_live

Setup users and user roles as needed in AWS Console > IAM accounts

6.  Launch with a -o web command, with the right user, as 

python ./aws-inspector.py -o web


LICENSE
---------
This product is a proprietary software owned by BridgeApps UK Ltd, and is not to be shared outside the organization without prior permission.

