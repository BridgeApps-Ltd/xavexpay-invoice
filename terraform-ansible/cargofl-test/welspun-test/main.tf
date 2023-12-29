# main.tf

provider "google" {
  credentials = file("/home/ananthu/terraform-gcp.json")
  project     = "innoctive-gcp-test"
  region      = "asia-south1"  # Change to your desired region
}

module "vpc_subnet" {
  source       = "/home/terraform/cargofl-test/module/vpc_subnet"
  project      = "innoctive-gcp-test"  # Make sure to replace with your actual GCP project ID
  vpc_name     = "cargofl-test-welspun"
  subnet_name  = "cargofl-test-welspun-subnet"
  subnet_cidr  = "10.10.17.0/24"  # You may want to update this as needed
}

module "compute_instance" {
  source             = "/home/terraform/cargofl-test/module/compute_instance"
  project            = "innoctive-gcp-test"
  vpc_self_link      = module.vpc_subnet.vpc_self_link
  subnet_self_link   = module.vpc_subnet.subnet_self_link
  instance_name      = "welspun-test"


  allow_ssh          = "welspun-test-allow-ssh"
  allow_https        = "welspun-test-allow-https"
  allow_http         = "welspun-test-allow-http"
  allow_9090         = "welspun-test-allow-9090"
}


