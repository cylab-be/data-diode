# Data Diode

[![Build Status](https://travis-ci.org/RUCD/data-diode.svg?branch=master)](https://travis-ci.org/RUCD/data-diode)

Developement takes place at https://gitlab.cylab.be/cylab/data-diode

## Packet forwarding

Can be achieved by

iptables -t nat -A PREROUTING -i $interface -p udp --dport $input_port -j DNAT --to $destination:$output_port
iptables -t nat -A POSTROUTING -o $interface -j MASQUERADE

## Far End Fault (FEF)

Far End Fault (FEF) is a part of the IEEE 802.3u standard (Fast Ethernet). When a media converter stops receiving a signal, it will stop emiting as wel , thus bringing the connection down in both directions.

This is not desirable for a data diode.

* https://www.etherwan.com/support/featured-articles/link-fault-pass-through
* https://store.moxa.com/a/know/article/using-fiber-media-converters-with-copper-networks?no=DC20130626134707746

This mechanism is implemented by most modern media converters. However, some media converters have a dip switch that allows to turn this feature off:

* https://www.transition.com/products/media-converters/sgetf10xx-110-series/#

According to some sources, this function may also be auto-disabled when different media converters are used: https://store.moxa.com/a/know/article/using-fiber-media-converters-with-copper-networks?no=DC20130626134707746
