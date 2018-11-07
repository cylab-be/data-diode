# Data Diode

[![Build Status](https://travis-ci.org/RUCD/data-diode.svg?branch=master)](https://travis-ci.org/RUCD/data-diode)

Developement takes place at https://gitlab.cylab.be/cylab/data-diode

## Packet forwarding

Can be achieved by

sudo nano /etc/sysctl.conf
net.ipv4.ip_forward=1
and 
sudo sysctl -p

iptables -t nat -A PREROUTING -i $interface -p udp --dport $input_port -j DNAT --to $destination:$output_port
iptables -t nat -A POSTROUTING -o $interface -j MASQUERADE

Attention:

* MASQUERADE is required, otherwize the packet may be considered as a martian by the next router (diode out): https://en.wikipedia.org/wiki/Martian_packet
* the nat table is checked only once when connection is established! For UDP packets, conntrack keeps a timeout => after adding rules, you may need to reboot the router: https://serverfault.com/a/875734


## Far End Fault (FEF)

Far End Fault (FEF) is a part of the IEEE 802.3u standard (Fast Ethernet). When a media converter stops receiving a signal, it will stop emiting as wel , thus bringing the connection down in both directions.

This is not desirable for a data diode.

* https://www.etherwan.com/support/featured-articles/link-fault-pass-through
* https://store.moxa.com/a/know/article/using-fiber-media-converters-with-copper-networks?no=DC20130626134707746

This mechanism is implemented by most modern media converters. However, some media converters have a dip switch that allows to turn this feature off:

* https://www.transition.com/products/media-converters/sgetf10xx-110-series/#

According to some sources, this function may also be auto-disabled when different media converters are used: https://store.moxa.com/a/know/article/using-fiber-media-converters-with-copper-networks?no=DC20130626134707746
