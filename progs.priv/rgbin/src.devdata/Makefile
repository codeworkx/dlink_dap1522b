# makefile for devdata

PROGNAME:=devdata

all: $(PROGNAME)

objects: $(PROGNAME).o

$(PROGNAME): Makefile $(PROGNAME).c
	$(Q)$(CC) -c -Wall $(PROGNAME).c -I../../../include
	$(Q)$(CC) -o $(PROGNAME) $(PROGNAME).o

$(PROGNAME).o: Makefile $(PROGNAME).c
	$(Q)$(CC) -c $(CFLAGS) -I.. $(PROGNAME).c

clean:
	$(Q)rm -f *.o *.gdb *~ *.elf $(PROGNAME)

.PHONY: all objects clean
