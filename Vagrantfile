Vagrant.configure("2") do |config|

  config.vm.define "unsecure" do |unsecure|
    unsecure.vm.box = "ubuntu/xenial64"
    unsecure.vm.network "private_network", ip: "192.168.100.10"
    unsecure.vm.provision "shell", path: "vagrant/unsecure.sh"
  end

  config.vm.define "diodein" do |diodein|
    diodein.vm.box = "ubuntu/xenial64"
    diodein.vm.network "forwarded_port", guest: 80, host: 8081, host_ip: "127.0.0.1"
    diodein.vm.network "private_network", ip: "192.168.100.11"
    diodein.vm.network "private_network", ip: "192.168.101.1"
    diodein.vm.provision "shell", path: "vagrant/diode-in.sh"
  end

  config.vm.define "diodeout" do |diodeout|
    diodeout.vm.box = "ubuntu/xenial64"
    diodeout.vm.network "forwarded_port", guest: 80, host: 8082, host_ip: "127.0.0.1"
    diodeout.vm.network "forwarded_port", guest: 8000, host: 8083, host_ip: "127.0.0.1"
    diodeout.vm.network "private_network", ip: "192.168.101.2", mac: "AABBCCDDEEFF"
    diodeout.vm.network "private_network", ip: "192.168.102.1"
    diodeout.vm.provision "shell", path: "vagrant/diode-out.sh"
  end

  config.vm.define "secure" do |secure|
    secure.vm.box = "ubuntu/xenial64"
    secure.vm.network "private_network", ip: "192.168.102.10"
    secure.vm.provision "shell", path: "vagrant/secure.sh"
  end

  config.vm.define "dev" do |dev|
    dev.vm.box = "ubuntu/xenial64"
    dev.vm.network "forwarded_port", guest: 80, host: 8080, host_ip: "127.0.0.1"
    dev.vm.network "private_network", ip: "0.0.0.0", auto_config: false
    dev.vm.network "private_network", ip: "0.0.0.0", auto_config: false
    dev.vm.provision "shell", path: "vagrant/dev.sh"
    dev.vm.provision "shell", path: "vagrant/restart-networking.sh", run: "always"
    dev.vm.synced_folder "./", "/var/www/data-diode", owner: "www-data", group: "www-data"
  end

end
