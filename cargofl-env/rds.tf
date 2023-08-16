provider "aws" {
  access_key = ""
  secret_key = ""
  region     = "ap-south-1"  # Mumbai region
}

resource "aws_vpc" "example_vpc" {
  cidr_block = "10.0.0.0/16"
  enable_dns_support = true
  enable_dns_hostnames = true

  tags = {
    Name = "ExampleVPC"
  }
}

resource "aws_subnet" "public_subnet" {
  count = 3  # Create three public subnets in different AZs
  vpc_id     = aws_vpc.example_vpc.id
  cidr_block = "10.0.${count.index}.0/24"
  availability_zone = "ap-south-1${element(["a", "b", "c"], count.index)}"

  tags = {
    Name = "PublicSubnet${count.index + 1}"
  }
}

resource "aws_security_group" "example_security_group" {
  vpc_id = aws_vpc.example_vpc.id

  egress {
    from_port   = 0
    to_port     = 0
    protocol    = "-1"
    cidr_blocks = ["0.0.0.0/0"]
  }

  ingress {
    from_port   = 3306
    to_port     = 3306
    protocol    = "tcp"
    cidr_blocks = ["0.0.0.0/0"]
  }

  tags = {
    Name = "ExampleSecurityGroup"
  }
}

resource "aws_internet_gateway" "example_igw" {
  vpc_id = aws_vpc.example_vpc.id
}

resource "aws_route_table" "public_route_table" {
  vpc_id = aws_vpc.example_vpc.id

  route {
    cidr_block = "0.0.0.0/0"
    gateway_id = aws_internet_gateway.example_igw.id
  }
}

resource "aws_route_table_association" "public_subnet_association" {
  count = 3
  subnet_id      = aws_subnet.public_subnet[count.index].id
  route_table_id = aws_route_table.public_route_table.id
}

resource "aws_db_subnet_group" "example_db_subnet_group" {
  name       = "example-db-subnet-group"
  subnet_ids = aws_subnet.public_subnet[*].id
}

resource "aws_db_instance" "example_rds" {
  identifier             = "example-rds-instance"
  allocated_storage      = 20
  storage_type           = "gp2"
  engine                 = "mysql"
  engine_version         = "5.7"
  instance_class         = "db.t2.micro"
  db_name                = "exampledb"
  username               = "admin"
  password               = "sghsg445tr"
  publicly_accessible   = true  # Set RDS instance to be publicly accessible
  vpc_security_group_ids = [aws_security_group.example_security_group.id]
  db_subnet_group_name   = aws_db_subnet_group.example_db_subnet_group.name

  tags = {
    Name = "ExampleRDSInstance"
  }
}
