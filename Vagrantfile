Vagrant.configure("2") do |config|
  if Vagrant.has_plugin?("vagrant-proxyconf")
    config.proxy.http = "http://10.67.1.60:3128/"
    config.proxy.https = "http://10.67.1.60:3128/"
  end

  config.vm.define "mirror" do |mirror|
    mirror.vm.box = "ubuntu/xenial64"
    mirror.vm.network "forwarded_port", guest: 80, host: 8080, host_ip: "127.0.0.1"
    mirror.vm.network "private_network", ip: "192.168.100.10"
    mirror.vm.provision "shell", path: "vagrant/mirror.sh"
  end

  config.vm.define "secure" do |secure|
    secure.vm.box = "ubuntu/xenial64"
    secure.vm.network "private_network", ip: "192.168.100.11"
    secure.vm.provision "shell", path: "vagrant/mirror-secure.sh"
  end


end
