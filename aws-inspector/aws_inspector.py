import pandas as pd
import json
import boto3
import socket

class AWSInspector:


    def __init__(self, profile_name):

        self.profile_name = profile_name
        boto3.setup_default_session(profile_name=self.profile_name)
        # Initialize with new profile
        self.ec2 = boto3.client('ec2')
        self.rds = boto3.client('rds')
        self.s3 = boto3.client('s3')
        
    @property
    def current_profle(self):
        return boto3.session.Session().profile_name


    def describe_vpcs(self):
        vpc_json = []
        vpcs = self.ec2.describe_vpcs()
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
    def describe_vpcs_as_tree(self):
        vpc_json = []
        vpcs = self.ec2.describe_vpcs()
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
    def describe_ec2(self):
        instances = self.ec2.describe_instances()
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
    def describe_ec2_as_tree(self):
        instances = self.ec2.describe_instances()
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

                subnetid = data2["NetworkInterfaces"][0]["SubnetId"]
                secgroupname = data2["NetworkInterfaces"][0]["Groups"][0]["GroupName"]
                secgroupid = data2["NetworkInterfaces"][0]["Groups"][0]["GroupId"]

                cpu_arch=data2["Architecture"]
                instance_type=data2["InstanceType"]
                ami_image = data2["ImageId"]

                ip_address_public = socket.gethostbyname(pub_ip)
                ec2_ds = {
                    "id" : "ec2-" +instance_id,
                    "parent": "ec2-" + vpc_id
                }

                # collect tags 
                tag_ec2 = data2['Tags']
                for tagss in tag_ec2:
                    key_ec2 = tagss['Key']
                    value_ec2 = tagss['Value']
                    ec2_ds ["text"] = f"{kname} | {value_ec2} | {instance_id} " + "(link)"
                ec2_json.append(ec2_ds)

                # Add a detail line per System
                ec2_detail_ds1 = {
                    "id" : "ec2-" +instance_id +"-"+ instance_id+"1",
                    "parent": "ec2-" +instance_id,
                    "text" : "IP: " +  ip_address_public + " ( " + ip + " ) ",
                    "type" : "element"
                }
                ec2_json.append(ec2_detail_ds1)

                # Add sec group
                ec2_detail_ds2 = {
                    "id" : secgroupid+"--"+instance_id,
                    "parent":"ec2-" + instance_id,
                    "text" : "SecurityGroup: " + secgroupname,
                    "type" : "element"
                }
                ec2_json.append(ec2_detail_ds2)

                # Add subnet 
                ec2_detail_ds3 = {
                    "id" : "ec2-" +instance_id +"-"+ instance_id + "3",
                    "parent": "ec2-" +instance_id,
                    "text" : "SubnetID: " + subnetid,
                    "type" : "element"
                }
                ec2_json.append(ec2_detail_ds3)

                # Add subnet 
                ec2_detail_ds4 = {
                    "id" : "ec2-" + instance_id +"-"+ instance_id + "4",
                    "parent": "ec2-" +instance_id,
                    "text" : "System: " + instance_type + " (" + cpu_arch + ") | AMI: " + ami_image,
                    "type" : "element"
                }
                ec2_json.append(ec2_detail_ds4)

                 # Add subnet 
                ec2_detail_ds5 = {
                    "id" : "logs-"+pub_ip,
                    "parent": "ec2-" +instance_id,
                    "text" : "View Server Logs (link)" ,
                    "type" : "element"
                }
                ec2_json.append(ec2_detail_ds5)

        return ec2_json

    # Get all RDS Instances
    def describe_rds(self):
        resp = self.rds.describe_db_instances()
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
    def describe_rds_as_tree(self):
        resp = self.rds.describe_db_instances()
        resp = resp['DBInstances']
        rds_json = []
        for data in resp:
            subnet = data['DBSubnetGroup']
            instance_id = data['DBInstanceIdentifier']
            vpcid = subnet['VpcId']
            taglist = data['TagList']
            arn = data['DBInstanceArn']

            rds_ds = {
                    "id" : "rds-"+instance_id,
                    "parent": "rds-" + vpcid,
                    "text" : "DBServerName: " + instance_id  + "(link)"
                }

            for tags in taglist:
                key = tags['Key']
                value = tags['Value']
                rds_ds ["text"] = rds_ds ["text"] + "|" + value

            rds_json.append(rds_ds)

            # Add DB Detail
            rds_detail_ds2 = {
                "id" : "rds-" + instance_id + "2",
                "parent": "rds-"+instance_id,
                "text" : "ARN: " + arn
            }
            rds_json.append(rds_detail_ds2)

        return rds_json
        


    # Get all S3 End points 
    def describe_s3(self):
        s3_json = []
        response = self.ec2.describe_vpc_endpoints(
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

        return s3_json

    # Get all S3 End points 
    def describe_s3_as_tree(self):
        s3_json = []
        response = self.ec2.describe_vpc_endpoints(
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
        response = self.s3.list_buckets()

        response = response['Buckets']
        for data in response:
            name = data['Name']
            s3_ds = {
                        "id" : name,
                        "parent": "s3-" + vpcId,
                        "text" : f' {name}| {value}'
                    }
            s3_json.append (s3_ds)
        
        return s3_json 

    def describe(self):
        vpc_json = self.describe_vpcs()
        ec2_json = self.describe_ec2()
        rds_json = self.describe_rds()
        s3_json = self.describe_s3()
        return vpc_json, ec2_json, rds_json, s3_json

    def describe_for_tree(self):
        vpc_json = self.describe_vpcs_as_tree()
        ec2_json = self.describe_ec2_as_tree()
        rds_json = self.describe_rds_as_tree()
        s3_json = self.describe_s3_as_tree()
        return vpc_json, ec2_json, rds_json, s3_json