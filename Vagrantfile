Vagrant.configure("2") do |config|
  config.vm.define "diodein" do |diodein|
    diodein.vm.box = "ubuntu/xenial64"
    diodein.vm.network "forwarded_port", guest: 80, host: 8081, host_ip: "127.0.0.1"
    diodein.vm.network "private_network", ip: "192.168.101.10"
    diodein.vm.network "private_network", ip: "192.168.102.10"
    diodein.vm.provision "shell", path: "diodein.sh"
  end
  
  config.vm.define "diodeout" do |diodeout|
    diodeout.vm.box = "ubuntu/xenial64"
    diodeout.vm.network "forwarded_port", guest: 80, host: 8082, host_ip: "127.0.0.1"
    diodeout.vm.network "private_network", ip: "192.168.101.11"
    diodeout.vm.network "private_network", ip: "192.168.102.11"
    diodeout.vm.provision "shell", path: "diodeout.sh"
  end
  
end
