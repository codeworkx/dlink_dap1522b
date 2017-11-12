#
# rgcfg2
#

all: rgcfg2

objects: rgcfg2.o

rgcfg2: Makefile rgcfg2.c
	$(Q)$(CC) -c rgcfg2.c -I../../../include
	$(Q)$(CC) -o rgcfg2 rgcfg2.o

rgcfg2.o: Makefile rgcfg2.c
	$(Q)$(CC) -c $(CFLAGS) -I.. rgcfg2.c

clean:
	$(Q)rm -f *.o *.gdb *~ *.elf rgcfg2

.PHONY: all objects clean
