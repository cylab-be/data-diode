#!/bin/bash
interface=$2
input_port=$3
destination=$4
output_port=$5
case $1 in
    "add")
        iptables -t nat -A PREROUTING -i $interface -p udp --dport $input_port -j DNAT --to $destination:$output_port
        ;;
    "remove")
        iptables -t nat -D PREROUTING -i $interface -p udp --dport $input_port -j DNAT --to $destination:$output_port
        ;;
    "save")
        iptables-save > /etc/iptables/rules.v4 \
        && ip6tables-save > /etc/iptables/rules.v6
        ;;
    *)
        exit 1
        ;;
esac
exit 0