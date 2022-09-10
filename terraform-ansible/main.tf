module "trent-test" {
        source = "./modules/templates/test/trent-test"
	gitpass = var.gitpass
    }

/*
module "trent-prod" {
        source = "./modules/templates/production/trent-prod"
        gitpass = var.gitpass
    }
*/
