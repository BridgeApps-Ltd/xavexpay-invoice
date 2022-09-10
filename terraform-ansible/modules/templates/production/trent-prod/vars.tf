variable "imageid" {
  default = "ami-0cfedf42e63bba657"
}

variable "instanceType" {
  default = "t2.micro"
}

variable "security_grp" {
  default = "default"
}

variable "user" {
  default = "ubuntu"
}

variable "homePath" {
  default = "/home/ubuntu"
}

variable "keyPath" {}

variable "key_name" {
  default = "mubinAWS"
}

variable "gitpass" {}

variable "instance_name" {
        default = "trent-prod"
}
