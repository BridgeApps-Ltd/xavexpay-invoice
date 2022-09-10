terraform {
  required_providers {
    aws = {
      source  = "hashicorp/aws"
      version = "4.15.1"
    }
  }
}


variable "aws_access_key" {}
variable "aws_secret_key" {}
variable "aws_region" {}

provider "aws" {
  # Configuration options
  access_key = var.aws_access_key
  secret_key = var.aws_secret_key
  region     = var.aws_region
}


