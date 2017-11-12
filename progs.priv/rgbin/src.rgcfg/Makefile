
all: rgcfg

objects: rgcfg.o

rgcfg: Makefile rgcfg.c
	$(Q)$(CC) -c rgcfg.c -I../../../include
	$(Q)$(CC) -o rgcfg rgcfg.o

rgcfg.o: Makefile rgcfg.c
	$(Q)$(CC) -c $(CFLAGS) -I.. rgcfg.c

clean:
	$(Q)rm -f *.o *.gdb *~ *.elf rgcfg

.PHONY: all objects clean
