import socket
import boto3
import pandas as pd
import json
import os
import argparse
from flask import Flask, render_template, request
import warnings

warnings.filterwarnings("ignore")

args = {}

ec2 = boto3.client('ec2')
rds = boto3.client('rds')
s3 = boto3.client('s3')

absolute_path = os.path.dirname(os.path.abspath(__file__))


all_services_data = {}
s3_bucket_data = []

app = Flask(__name__)

# reset all global variables 
def resetAll():
    return []


# Describe VPCs first
def describeVPCs():
    vpc_json = []
    vpcs = ec2.describe_vpcs()
    vpcs = vpcs['Vpcs']
    for data in vpcs:
        vpcId = data['VpcId']
        tags = data['Tags']
        for tag in tags:
            key = tag['Key']
            value = tag['Value']
            vpc_json.append({
                    "VPCID":"{}".format(vpcId),
                    "Tag":f"VPC:{key} | Customer:{value}", 
                    "Service":"VPC"
                    })
    return vpc_json

# Describe VPCs first
def describeVPCsAsTree():
    vpc_json = []
    vpcs = ec2.describe_vpcs()
    vpcs = vpcs['Vpcs']

    for data in vpcs:
        vpcId = data['VpcId']
        vpcDataset = {
            "id" : vpcId,
            "parent": "#"
        }
        tags = data['Tags']
        vpc_desc = "VPC"
        for tag in tags:
            key = tag['Key']
            value = tag['Value']
            vpc_tag_desc =  " | " + tag['Value'] 
            vpc_desc = vpc_desc + vpc_tag_desc
        vpcDataset["text"] = vpc_desc
        vpc_json.append(vpcDataset)

        # Sublist EC2
        ec2ParentDs = {
            "id" : "ec2-" + vpcId,
            "parent": vpcId,
            "text" : "ec2"
        }
        vpc_json.append(ec2ParentDs)

        # Sublist RDS
        rdsParentDs = {
            "id" : "rds-" + vpcId,
            "parent": vpcId,
            "text" : "rds"
        }
        vpc_json.append(rdsParentDs)

        # Sublist S3
        s3ParentDs = {
            "id" : "s3-" + vpcId,
            "parent": vpcId,
            "text" : "s3"
        }
        vpc_json.append(s3ParentDs)

    return vpc_json

# Get all EC2 Instances
def describeEC2():
    instances = ec2.describe_instances()
    instances = instances['Reservations']
    ec2_json = []
    for data1 in instances:
        instances_details = data1['Instances']
        for data2 in instances_details:
            instance_id = data2['InstanceId']
            ip = data2['PrivateIpAddress']
            pub_ip = data2["PublicDnsName"]
            kname = data2["KeyName"]
            vpc_id = data2['VpcId']
            ip_address_public = socket.gethostbyname(pub_ip)

            # 
            tag_ec2 = data2['Tags']
            for tagss in tag_ec2:
                key_ec2 = tagss['Key']
                value_ec2 = tagss['Value']
                ec2_json.append({"ServiceId or ARN":"{}".format(instance_id),
                    "VPCID": "{}".format(vpc_id),
                    "Tag":f"{kname}|{key_ec2}:{value_ec2}",
                    "Service" : "EC2",
                    "Public IP": "{}".format(ip_address_public), 
                        "VPCID":"{}".format(vpc_id),
                        "Private IP": "{}".format(ip)
                    })
    return ec2_json

# Get all EC2 Instances
def describeEC2AsTree():
    instances = ec2.describe_instances()
    instances = instances['Reservations']
    ec2_json = []
    for data1 in instances:
        instances_details = data1['Instances']
        for data2 in instances_details:
            instance_id = data2['InstanceId']
            ip = data2['PrivateIpAddress']
            pub_ip = data2["PublicDnsName"]
            kname = data2["KeyName"]
            vpc_id = data2['VpcId']
            ip_address_public = socket.gethostbyname(pub_ip)
            ec2_ds = {
                "id" : instance_id,
                "parent": "ec2-" + vpc_id
            }

            # collect tags 
            tag_ec2 = data2['Tags']
            for tagss in tag_ec2:
                key_ec2 = tagss['Key']
                value_ec2 = tagss['Value']
                ec2_ds ["text"] = f"{kname} | {value_ec2} | {instance_id}"
            ec2_json.append(ec2_ds)

            # Add a detail line per System
            ec2_detail_ds = {
                "id" : instance_id +"-"+ instance_id,
                "parent": instance_id,
                "text" : ip_address_public + " ( " + ip + " ) "
            }
            ec2_json.append(ec2_detail_ds)

    return ec2_json

# Get all RDS Instances
def describeRDS():
    resp = rds.describe_db_instances()
    resp = resp['DBInstances']
    rds_json = []
    for data in resp:
        subnet = data['DBSubnetGroup']
        name = data['DBInstanceIdentifier']
        vpcid = subnet['VpcId']
        taglist = data['TagList']
        arn = data['DBInstanceArn']
        for tags in taglist:
            key = tags['Key']
            value = tags['Value']
            rds_json.append({'VPCID':f'{vpcid}',
                'Service': 'rds', 
                'ServiceId or ARN': f'{arn}',
                'Tag': f'{name} | {key}:{value}',
                'Private IP' : '',
                'Public IP' : '' })
    return rds_json
    
# Get all RDS Instances
def describeRDSAsTree():
    resp = rds.describe_db_instances()
    resp = resp['DBInstances']
    rds_json = []
    for data in resp:
        subnet = data['DBSubnetGroup']
        instance_id = data['DBInstanceIdentifier']
        vpcid = subnet['VpcId']
        taglist = data['TagList']
        arn = data['DBInstanceArn']

        rds_ds = {
                "id" : instance_id,
                "parent": "rds-" + vpcid
            }

        for tags in taglist:
            key = tags['Key']
            value = tags['Value']
            rds_ds ["text"] = f'{arn} | {value} | {instance_id}'

        rds_json.append(rds_ds)

        print(rds_json)

    return rds_json
    


# Get all S3 End points 
def describeS3():
    s3_json = []
    response = ec2.describe_vpc_endpoints(
        Filters=[
            {
                'Name': 'service-name',
                'Values': ['com.amazonaws.ap-south-1.s3']
            }
        ]
    )
    response = response['VpcEndpoints']

    for data in response:
        vpcId =data['VpcId']
        tags = data['Tags']
        for tag in tags:
            key = tag['Key']
            value = tag['Value']
        s3_json.append({"VPCID": f'{vpcId}',"Service":"s3", "Tag": f'Name:{key}|Customer:{value}', 'Public IP' : '', 'Private IP' : '', 'ServiceId or ARN' : ''})

    # Qt : Why again - which one is right? 
    response = s3.list_buckets()

    response = response['Buckets']
    for data in response:
        name = data['Name']
        s3_bucket_data.append(name)

    return s3_json

# Get all S3 End points 
def describeS3AsTree():
    s3_json = []
    response = ec2.describe_vpc_endpoints(
        Filters=[
            {
                'Name': 'service-name',
                'Values': ['com.amazonaws.ap-south-1.s3']
            }
        ]
    )
    response = response['VpcEndpoints']

    for data in response:
        vpcId =data['VpcId']
        tags = data['Tags']
        for tag in tags:
            key = tag['Key']
            value = tag['Value']
    
    # Qt : Why again - which one is right? 
    response = s3.list_buckets()

    response = response['Buckets']
    for data in response:
        name = data['Name']
        s3_bucket_data.append(name)
        s3_ds = {
                    "id" : name,
                    "parent": "s3-" + vpcId,
                    "text" : f' {name}| {value}'
                }
        s3_json.append (s3_ds)
    
    return s3_json
# -----

# Describe each components Individually
def describeAllComponents():
        vpc_json = describeVPCs()
        ec2_json = describeEC2()
        rds_json = describeRDS()
        s3_json = describeS3()
        return vpc_json, ec2_json, rds_json, s3_json


def mergeAllData(ec2_json, rds_json, s3_json):
    ec2_df = pd.DataFrame(ec2_json)
    s3_df = pd.DataFrame(s3_json)
    rds_df = pd.DataFrame(rds_json)
    df = pd.concat([ec2_df, s3_df, rds_df])
    all_services_data = json.loads(df.to_json(orient='records'))
    # print(all_services_data)
    # print(df.shape)
    return all_services_data

# -----

# Describe each components Individually
def describeAllComponentsForTree():
        vpc_json = describeVPCsAsTree()
        ec2_json = describeEC2AsTree()
        rds_json = describeRDSAsTree()
        s3_json = describeS3AsTree()
        return vpc_json, ec2_json, rds_json, s3_json
        

def mergeAllDataForTree(vpc_json, ec2_json,rds_json,s3_json):
    vpc_df = pd.DataFrame(vpc_json)
    ec2_df = pd.DataFrame(ec2_json)
    s3_df = pd.DataFrame(s3_json)
    rds_df = pd.DataFrame(rds_json)
    df = pd.concat([vpc_df, ec2_df, rds_df, s3_df])
    all_services_data = json.loads(df.to_json(orient='records'))
    # print(all_services_data)
    # print(df.shape)
    return all_services_data

# -----


# Print files 
def printOutToFile(outputpath):

    # Decide on outputpath, specific to OS
    if (outputpath == '.'):
        filepath = absolute_path + '/output'
    else:
        filepath = outputpath

    print("... Creating file : " + filepath + '/merged.json' )

    # Print out a Merged File 
    with open(filepath + '/merged.json', 'w') as outfile:
        json.dump(all_services_data, outfile)

    print("... Creating file : " + filepath + '/buckets.json' )

    # Print out a Merged File for S3 buckets
    with open(filepath + '/buckets.json', 'w') as outfile:
        json.dump(s3_bucket_data, outfile)


# Print out file content as CSV
# ** NOT USED now **
def printOutputAsCsv(outputpath):

    # Decide on outputpath, specific to OS
    if (outputpath == '.'):
        filepath = absolute_path + '/output'
    else:
        filepath = outputpath

    print("... Creating file : " + filepath + '/merged.csv' )

    with open('all_services_data.json', 'w') as f:
        json.dump(all_services_data, f)
    df = pd.read_json('all_services_data.json')
    df.to_csv('all_services_data.csv')

    print("... Creating file : " + filepath + '/buckets.csv' )

    with open('s3_bucket_data.json', 'w') as f:
        json.dump(s3_bucket_data, f)
    df = pd.read_json('s3_bucket_data.json')
    df.to_csv('s3_bucket_data.csv')

def getAllAwsProfiles():
    all_aws_profiles = boto3.session.Session().available_profiles
    for profile in all_aws_profiles:
        print("... Available Profiles : " + profile)
    return all_aws_profiles

def getCurrentAwsProfile():
    return boto3.session.Session().profile_name

def setAwsProfile(new_aws_profile):
    print ('Setting up new session to = ' + new_aws_profile)
    boto3.session.Session(profile_name=new_aws_profile)
    print('Current Session set as = '+ boto3.session.Session().profile_name)


def processInstanceInfo(instance_id):
    instance_info = ec2.describe_instances(InstanceIds=[instance_id])
    return instance_info

    # write code to read from the instance_info
    # and dump all the contents in any format - json or text
    # so that the same can be returned back to the API > page.


# ---------------------------------------------------------

@app.route('/', methods=['GET'])
def page_home():
   return render_template('index.html')

@app.route('/tree', methods=['GET'])
def page_home_tree():
   return render_template('indextree.html')


@app.route('/api/v1/aws/services/all', methods=['GET'])
def api_all_services_data():
    resetAll()
    vpc_json, ec2_json, rds_json, s3_json = describeAllComponents()
    all_services_data = mergeAllData(ec2_json, rds_json, s3_json)
    print(len(all_services_data))
    return all_services_data

@app.route('/api/v1/aws/services/tree/all', methods=['GET'])
def api_all_services_tree_data():
    # resetAll()
    vpc_json, ec2_json, rds_json, s3_json = describeAllComponentsForTree()
    all_services_data = mergeAllDataForTree(vpc_json, ec2_json, rds_json, s3_json)
    return all_services_data


@app.route('/api/v1/aws/services/tree/<instance_id>', methods=['GET'])
def api_instance_details(instance_id):
    instance_info = processInstanceInfo(instance_id)
    return instance_info

@app.route('/api/v1/aws/services/s3', methods=['GET'])
def api_s3_bucket_data():
    describeS3()
    mergeAllData()
    return s3_bucket_data

@app.route('/api/v1/aws/services/reset', methods=['GET'])
def api_reset_contents():
    resetAll()
    return ""

@app.route('/api/v1/aws/profile/<profile_name>', methods=['POST'])
def api_set_new_aws_profile(profile_name):
    setAwsProfile(profile_name)
    return "Success: New Profile set : "

@app.route('/api/v1/aws/profile/<profile_name>', methods=['GET'])
def api_list_aws_profiles(profile_name):
    # all - is the only parameter processed for now
    if (profile_name == 'all'):
        all_aws_profiles = getAllAwsProfiles()
        return all_aws_profiles
    else:
        return 'Error: no profile mentioned'
        

# ---------------------------------------------------------

# Collect User Inputs on how to execute this script
def collectInputs():
    parser = argparse.ArgumentParser("aws-inspector")
    parser.add_argument('-o','--output', help="Output. Options - web | file. Default file content format is JSON", type=str, default='web', required=True)
    parser.add_argument('-op','--outputpath', help="Output Path to store output",   type=str, default='.', required=False)

    args = vars(parser.parse_args())
    return args


def main():
    print("\nUse this service to Inspect all assets in an AWS Account. \
Either print AWS Assets as a CSV or to get data to UI as JSON\n")

    # Collect User inputs
    args = collectInputs()

    print('... Working with AWS Session : ' +  getCurrentAwsProfile())

    # Process based on output
    print("... Initializing Data collection")
    if (args['output'] == 'web'):
        app.debug = True
        app.run()
    else:
        print('... Option Selected: File based. Printing all of AWS Assets as JSON')
        describeAllComponents()
        mergeAllData()
        printOutToFile(args['outputpath'])

    print("... Data collection completed")

if __name__ == "__main__":
    main()

