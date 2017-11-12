
all: getsys

objects: getsys.o

getsys: Makefile getsys.c
	$(Q)$(CC) -c -I.. ../lrgbin.c
	$(Q)$(CC) -c -I.. getsys.c
	$(Q)$(CC) -o getsys getsys.o lrgbin.o

getsys.i386: Makefile getsys.c
	$(Q)cc -c -I.. ../lrgbin.c
	$(Q)cc -c -I.. getsys.c
	$(Q)cc -o getsys getsys.o lrgbin.o

getsys.o: Makefile getsys.c
	$(Q)$(CC) -c $(CFLAGS) -I.. getsys.c

clean:
	$(Q)rm -f *.o *.gdb *~ *.elf getsys

install:
	$(Q)install -c getsys $(TARGET)/usr/sbin/

uninstall:
	$(Q)rm -f $(TARGET)/usr/sbin/rgdb

.PHONY: all objects clean
