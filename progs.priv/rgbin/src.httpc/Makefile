# makefile for httpc

PROGNAME:=httpc

all: $(PROGNAME)

objects: $(PROGNAME).o

$(PROGNAME): Makefile $(PROGNAME).c
	$(Q)$(CC) -c -I.. ../lrgbin.c
	$(Q)$(CC) -c $(PROGNAME).c -I.. -I../../../include
	$(Q)$(CC) -o $(PROGNAME) lrgbin.o $(PROGNAME).o

$(PROGNAME).o: Makefile $(PROGNAME).c
	$(Q)$(CC) -c $(CFLAGS) -I.. $(PROGNAME).c

clean:
	$(Q)rm -f *.o *.gdb *.elf $(PROGNAME)

.PHONY: all objects clean
