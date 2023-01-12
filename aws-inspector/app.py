from re import I
import socket
import boto3
import pandas as pd
import json
import os
import argparse
from flask import Flask, render_template, request, redirect
import warnings

warnings.filterwarnings("ignore")
from aws_inspector import AWSInspector

args = {}

absolute_path = os.path.dirname(os.path.abspath(__file__))

aws_inspector = None

all_services_data = {}
s3_bucket_data = []

app = Flask(__name__)

def merge_data(data_list):
    all_dfs = []
    for json_data in data_list:
        all_dfs.append(pd.DataFrame(json_data))
    df = pd.concat(all_dfs)
    all_services_data = json.loads(df.to_json(orient='records'))
    return all_services_data

def process_instance_info(instance_id):
    if (instance_id.startswith('ec2-')):
        # EC2 Instance
        instance_id = instance_id.removeprefix('ec2-')
        instance_info = aws_inspector.ec2.describe_instances(InstanceIds=[instance_id])
    elif (instance_id.startswith('rds-')):
        # rds
        instance_id = instance_id.removeprefix('rds-')
        instance_info = aws_inspector.rds.describe_db_instances(DBInstanceIdentifier=instance_id)
    elif (instance_id.startswith('sg-')):
        # security group
        instance_id = instance_id.split('--',1)[0] # remove ec2 instanceid after --
        instance_info = aws_inspector.ec2.describe_security_groups(GroupIds=[instance_id])
    elif (instance_id.startswith('logs-')):
        # security group
        instance_id = instance_id.removeprefix('logs-')
        instance_info = '<a href=redirect_to_url/'+ instance_id + ' target=_blank>"View Logs</a>'
    else:
        instance_info = "No information is retrieved for this AWS Service type" 

    return instance_info

def merge_all_data(ec2_json, rds_json, s3_json):
    ec2_df = pd.DataFrame(ec2_json)
    s3_df = pd.DataFrame(s3_json)
    rds_df = pd.DataFrame(rds_json)
    df = pd.concat([ec2_df, s3_df, rds_df])
    all_services_data = json.loads(df.to_json(orient='records'))
    return all_services_data

def get_all_aws_profiles():
    all_aws_profiles = boto3.session.Session().available_profiles
    for profile in all_aws_profiles:
        print("... Available Profiles : " + profile)
    return all_aws_profiles

def set_aws_profile(new_aws_profile):
    #os.environ["AWS_DEFAULT_PROFILE"] = new_aws_profile
    global aws_inspector
    aws_inspector = AWSInspector(profile_name=new_aws_profile)
    print(f'==>> Current Session set as = {aws_inspector.current_profle}')

    return "Successfully set profile to : " + new_aws_profile


@app.route('/', methods=['GET'])
def page_home():
   return render_template('index.html')

@app.route('/redirect_to_url/<url_to_redirect>', methods=['GET'])
def page_redirect_to_url(url_to_redirect):
    return redirect("http://" + url_to_redirect, code=302)


@app.route('/tree', methods=['GET'])
def page_home_tree():
   return render_template('indextree.html')


@app.route('/api/v1/aws/services/all', methods=['GET'])
def api_all_services_data():
    vpc_json, ec2_json, rds_json, s3_json = aws_inspector.describe()
    all_services_data = merge_data([ec2_json, rds_json, s3_json])
    return all_services_data

@app.route('/api/v1/aws/services/tree/all', methods=['GET'])
def api_all_services_tree_data():
    # resetAll()
    vpc_json, ec2_json, rds_json, s3_json = aws_inspector.describe_for_tree()
    all_services_data = merge_data([vpc_json, ec2_json, rds_json, s3_json])
    return all_services_data


@app.route('/api/v1/aws/services/tree/<instance_id>', methods=['GET'])
def api_instance_details(instance_id):
    instance_info = process_instance_info(instance_id)
    return instance_info

@app.route('/api/v1/aws/profile/<profile_name>', methods=['POST'])
def api_set_new_aws_profile(profile_name):
    status = set_aws_profile(profile_name)
    return status

@app.route('/api/v1/aws/profile/<profile_name>', methods=['GET'])
def api_list_aws_profiles(profile_name):
    # all - is the only parameter processed for now
    if (profile_name == 'all'):
        all_aws_profiles = get_all_aws_profiles()
        return all_aws_profiles
    else:
        return 'Error: no profile mentioned'

@app.route('/api/v1/aws/profile', methods=['GET'])
def api_get_current_aws_profile():
    return get_current_aws_profile()


def get_current_aws_profile():
    return aws_inspector.get_current_profile()
    
def parse_inputs():
    parser = argparse.ArgumentParser("aws-inspector")
    parser.add_argument('-o','--output', help="Output. Options - web | file. Default file content format is JSON", type=str, default='web', required=True)
    parser.add_argument('-op','--outputpath', help="Output Path to store output",   type=str, default='.', required=False)
    parser.add_argument('-p','--port', help="port to run service",   type=int, default=5000, required=False)
    args = vars(parser.parse_args())
    return args

def run_flask(port):
    app.debug = True
    app.run(port=port)

def write_to_file(data, outputpath):

    # Decide on outputpath, specific to OS
    if (outputpath == '.'):
        filepath = absolute_path + '/output'
    else:
        filepath = outputpath

    print("... Creating file : " + filepath + '/merged.json' )

    # Print out a Merged File 
    with open(filepath + '/merged.json', 'w') as outfile:
        json.dump(data, outfile)

    # # Print out a Merged File for S3 buckets
    # with open(filepath + '/buckets.json', 'w') as outfile:
    #     json.dump(s3_bucket_data, outfile)

def main():
    args = parse_inputs()
    
    all_aws_profiles = get_all_aws_profiles()
    set_aws_profile(all_aws_profiles[0])

    if args["output"] == "web":
        run_flask(args["port"])
    else:
        _, ec2_json, rds_json, s3_json = aws_inspector.describe()
        all_services_data = merge_data([ec2_json, rds_json, s3_json])
        write_to_file(all_services_data, args["outputpath"])
        


if __name__ == "__main__":
    main()

