# modules/compute_instance/main.tf
variable "project" {}
variable "vpc_self_link" {}
variable "subnet_self_link" {}
variable "instance_name" {}

variable "allow_ssh" {}
variable "allow_https" {}
variable "allow_http" {}
variable "allow_9090" {}


provider "google" {
  credentials = file("/home/ananthu/terraform-gcp.json")
  project     = var.project
}

resource "google_compute_instance" "my_instance" {
  name         = var.instance_name
  machine_type = "e2-small"
  zone         = "asia-south1-a"  # Change to your desired zone

  boot_disk {
    initialize_params {
      image = "ubuntu-os-cloud/ubuntu-2004-focal-v20220712"
    }
  }

  network_interface {
    network    = var.vpc_self_link
    subnetwork = var.subnet_self_link
    access_config {}

  }
}
resource "google_compute_firewall" "allow_ssh" {
  name    = var.allow_ssh
  network = var.vpc_self_link

  allow {
    protocol = "tcp"
    ports    = ["22"]
  }

  source_ranges = ["0.0.0.0/0"]
}

resource "google_compute_firewall" "allow_https" {
  name    = var.allow_https
  network = var.vpc_self_link

  allow {
    protocol = "tcp"
    ports    = ["443"]
  }

  source_ranges = ["0.0.0.0/0"]
}

resource "google_compute_firewall" "allow_http" {
  name    = var.allow_http
  network = var.vpc_self_link

  allow {
    protocol = "tcp"
    ports    = ["80"]
  }

  source_ranges = ["0.0.0.0/0"]
}

resource "google_compute_firewall" "allow_9090" {
  name    = var.allow_9090
  network = var.vpc_self_link

  allow {
    protocol = "tcp"
    ports    = ["9090"]
  }

  source_ranges = ["0.0.0.0/0"]
}
