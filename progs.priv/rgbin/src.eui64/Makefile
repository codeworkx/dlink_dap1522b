# makefile for eui64

PROGNAME:=eui64

all: $(PROGNAME)

objects: $(PROGNAME).o

$(PROGNAME): Makefile $(PROGNAME).c
	$(Q)$(CC) -c $(PROGNAME).c -I../../../include
	$(Q)$(CC) -o $(PROGNAME) $(PROGNAME).o

$(PROGNAME).o: Makefile $(PROGNAME).c
	$(Q)$(CC) -c $(CFLAGS) -I.. $(PROGNAME).c

clean:
	$(Q)rm -f *.o *.gdb *~ *.elf $(PROGNAME)

.PHONY: all objects clean

