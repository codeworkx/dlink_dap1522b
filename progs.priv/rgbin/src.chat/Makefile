# makefile for 3g chat

PROGNAME:=chat

all: $(PROGNAME)

objects: $(PROGNAME).o

$(PROGNAME): Makefile $(PROGNAME).c
	$(Q)$(CC) -c $(PROGNAME).c -I../../../include
#	$(Q)$(CC) -c -I../../../include ../../../comlib/libxmldbc.c -o libxmldbc.o
#	$(Q)$(CC) -o $(PROGNAME) $(PROGNAME).o libxmldbc.o
	$(Q)$(CC) -o $(PROGNAME) $(PROGNAME).o 

$(PROGNAME).o: Makefile $(PROGNAME).c
	$(Q)$(CC) -c $(CFLAGS) -I.. $(PROGNAME).c

clean:
	$(Q)rm -f *.o *.gdb *~ *.elf $(PROGNAME)

.PHONY: all objects clean
