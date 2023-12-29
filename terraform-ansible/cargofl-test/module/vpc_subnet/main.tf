# modules/vpc_subnet/main.tf

variable "project" {}
variable "vpc_name" {}
variable "subnet_name" {}
variable "subnet_cidr" {}

provider "google" {
  credentials = file("/home/ananthu/terraform-gcp.json")
  project     = var.project
}

resource "google_compute_network" "vpc" {
  name                    = var.vpc_name
  auto_create_subnetworks = false
}

resource "google_compute_subnetwork" "subnet" {
  name          = var.subnet_name
  ip_cidr_range = var.subnet_cidr
  network       = google_compute_network.vpc.self_link
  region        = "asia-south1"  # Change to your desired region
}

output "vpc_self_link" {
  value = google_compute_network.vpc.self_link
}

output "subnet_self_link" {
  value = google_compute_subnetwork.subnet.self_link
}

