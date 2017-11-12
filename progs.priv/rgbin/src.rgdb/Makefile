
all: rgdb

objects: rgdb.o

rgdb: Makefile rgdb.c
	$(Q)$(CC) -c -I.. ../lrgbin.c
	$(Q)$(CC) -c -I.. rgdb.c
	$(Q)$(CC) -o rgdb rgdb.o lrgbin.o

rgdb.i386: Makefile rgdb.c
	$(Q)cc -c -I.. ../lrgbin.c
	$(Q)cc -c -I.. rgdb.c
	$(Q)cc -o rgdb rgdb.o lrgbin.o

rgdb.o: Makefile rgdb.c
	$(Q)$(CC) -c $(CFLAGS) -I.. rgdb.c

clean:
	$(Q)rm -f *.o *.gdb *~ *.elf rgdb

install:
	$(Q)install -c rgdb $(TARGET)/usr/sbin/

uninstall:
	$(Q)rm -f $(TARGET)/usr/sbin/rgdb

.PHONY: all objects clean
