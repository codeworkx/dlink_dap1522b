APPLETS := vethctl

build := $(notdir $(APPLETS))

.PHONY: all
all: $(build)
	@echo Done.

$(build): % : %.o
	@echo "Linking : $@"
	@$(CC) $(CFLAGS) $(LDFLAGS) $^ -o $@
#	@$(STRIP) $@
#
#	$(Q)$(CC) $(CFLAGS) $(EXTRA_CFLAGS) $(LDFLAGS) -o $@ $(SOURCE)
	$(Q)$(STRIP) --remove-section=.note --remove-section=.comment $@
	
%.o : %.c
	@echo "Building: $@"
	@$(CC) $(CFLAGS) -c -o $@ $<

.PHONY: clean
clean:
	@echo "Cleaning $(APPLETS)"
	@rm -f $(APPLETS) *.o
