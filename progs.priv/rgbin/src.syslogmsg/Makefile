
all: syslog_msg

objects: syslog_msg.o

syslog_msg: Makefile syslog_msg.c 
	$(Q)$(CC) -o syslog_msg syslogmsg.o

syslog_msg.o: Makefile syslog_msg.c
	$(Q)$(CC) -c $(CFLAGS) -I.. syslog_msg.c

clean:
	$(Q)rm -f *.o *.gdb *~ *.elf syslog_msg

.PHONY: all objects clean
