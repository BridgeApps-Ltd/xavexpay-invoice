#Speed Up Package Downloads with 'apt-fast'.
#apt-fast is a shell script wrapper for apt that accelerates package downloads by using multiple connections simultaneously via aria2.
package_name = ""

#➡ Installation:
sudo add-apt-repository ppa:apt-fast/stable; sudo apt update ; sudo apt install apt-fast

#➡ Usage:
sudo apt-fast install $package_name
