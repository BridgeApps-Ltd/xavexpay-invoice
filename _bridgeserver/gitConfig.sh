eval "$(ssh-agent -s)"
ssh-add ~/.ssh/kurudi.pem
ssh -T git@github.com
