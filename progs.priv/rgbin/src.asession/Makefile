
EXEC:=asession

all: $(EXEC)

objects: $(EXEC).o

$(EXEC): Makefile $(EXEC).c
	$(Q)$(CC) -c -I.. $(EXEC).c
	$(Q)$(CC) -c -I.. ../lrgbin.c
	$(Q)$(CC) -o $(EXEC) $(EXEC).o lrgbin.o

$(EXEC).o: Makefile $(EXEC).c
	$(Q)$(CC) -c $(CFLAGS) -I.. $(EXEC).c

clean:
	$(Q)rm -f *.o *.gdb *~ *.elf $(EXEC)

.PHONY: all objects clean
