# This CGI bin is first created for Aries.
# So I call it cgibin.aries.
-include ../../path.mk
-include ../arch.mk
-include ../../.config
#####################################################################
# source for CGI bin
MYNAME		:= CGI.BIN
APPLET		:= cgibin
LOCAL_SRCS	:= main.c libcgibin.c libsyslog.c 
CMLIB_SRCS	:= dtrace.c libxmldbc.c mem_helper.c strobj.c xstream.c md5.c
#####################################################################
# Variables
CGIS :=
CMDS :=
DIRS :=
CFLAGS += -I$(TOPDIR)/include
#####################################################################
# dtrace debug
DEBUG=n
ifeq ($(strip $(DEBUG)),y)
DDEBUG := y
DDEBUG_LEVEL := 0
endif
ifeq ($(strip $(DDEBUG)),y)
CFLAGS	+= -DDDEBUG
ifdef DDEBUG_LEVEL
CFLAGS	+= -DDBG_DEFAULT=$(DDEBUG_LEVEL)
endif
endif
#####################################################################
# Options
ifeq ($(strip $(CONFIG_CGIBIN_ARIES_PHPCGI)),y)
REQUIRE_DIR_USRSBIN	:= y
LOCAL_SRCS			+= phpcgi.c
CMDS				+= phpcgi
endif
ifeq ($(strip $(CONFIG_CGIBIN_ARIES_FILELOAD)),y)
REQUIRE_DIR_WEB		:= y
LOCAL_SRCS			+= fileload.c
CGIS				+= web/dlcfg.cgi web/seama.cgi web/fwup.cgi /web/dldongle.cgi /web/dlapn.cgi
endif
ifeq ($(strip $(CONFIG_CGIBIN_ARIES_FWUPDATER)),y)
REQUIRE_DIR_USRSBIN := y
LOCAL_SRCS			+= fwupdater.c
CMDS				+= fwupdater
endif
ifeq ($(strip $(CONFIG_CGIBIN_ARIES_SESSIONCGI)),y)
REQUIRE_DIR_WEB		:= y
LOCAL_SRCS			+= libsession.c session.c authentication.c hmac_md5.c
CGIS				+= web/session.cgi
CGIS				+= web/authentication.cgi
CGIS                += web/authentication_logout.cgi
CGIS                += web/webfa_authentication.cgi
CGIS                += web/webfa_authentication_logout.cgi
ifeq ($(strip $(CONFIG_CGIBIN_ARIES_CAPTCHACGI)),y)
CGIS				+= web/captcha.cgi
endif
endif
ifeq ($(strip $(CONFIG_CGIBIN_ARIES_HEDWIGCGI)),y)
REQUIRE_DIR_WEB		:= y
LOCAL_SRCS			+= hedwig.c
CGIS				+= web/hedwig.cgi
endif
ifeq ($(strip $(CONFIG_CGIBIN_ARIES_PIGWIDGEONCGI)),y)
REQUIRE_DIR_WEB		:= y
LOCAL_SRCS			+= pigwidgeon.c
CGIS				+= web/pigwidgeon.cgi
endif
ifeq ($(strip $(CONFIG_CGIBIN_ARIES_SERVICECGI)),y)
REQUIRE_DIR_WEB		:= y
LOCAL_SRCS			+= service.c
CGIS				+= web/service.cgi
endif
ifeq ($(strip $(CONFIG_CGIBIN_ARIES_SSDPCGI)),y)
REQUIRE_DIR_UPNP	:= y
LOCAL_SRCS			+= ssdpcgi.c
CGIS				+= upnp/ssdpcgi
endif
ifeq ($(strip $(CONFIG_CGIBIN_ARIES_SOAPCGI)),y)
REQUIRE_DIR_UPNP	:= y
REQUIRE_LIBIXML		:= y
LOCAL_SRCS			+= soapcgi.c
endif
ifeq ($(strip $(CONFIG_CGIBIN_ARIES_GENACGI)),y)
REQUIRE_DIR_UPNP	:= y
REQUIRE_LIBIXML		:= y
LOCAL_SRCS			+= genacgi.c
endif
ifeq ($(strip $(CONFIG_CGIBIN_ARIES_WFADEV)),y)
REQUIRE_DIR_UPNP	:= y
REQUIRE_LIBIXML		:= y
REQUIRE_BASE64		:= y
REQUIRE_LIBUSOCK	:= y
LOCAL_SRCS			+= wfadev.c
endif
ifeq ($(strip $(CONFIG_CGIBIN_ARIES_WFANOTIFY)),y)
REQUIRE_DIR_UPNP	:= y
REQUIRE_BASE64		:= y
REQUIRE_SLOOP		:= y
LOCAL_SRCS			+= wfanotify.c
CMDS				+= wfanotify
endif
ifeq ($(strip $(CONFIG_CGIBIN_ARIES_CONNTRACKCGI)),y)
REQUIRE_DIR_WEB		:= y
LOCAL_SRCS			+= conntrack.c
CGIS				+= web/conntrack.cgi
endif
ifeq ($(strip $(CONFIG_CGIBIN_ARIES_HNAP)),y)
REQUIRE_LIBIXML		:= y
REQUIRE_BASE64		:= y
LOCAL_SRCS			+= hnap.c
CMDS				+= hnap
endif
ifeq ($(strip $(CONFIG_CGIBIN_ARIES_SCANDIR)),y)
REQUIRE_DIR_WEB     := y
LOCAL_SRCS          += scandir/scandir.c scandir/misc.c
CMDS                += scandir.sgi
endif
ifeq ($(strip $(ELBOX_TEMPLATE_ARIES_MYDLINK_SUPPORT)),y)
REQUIRE_DIR_MYDLINK	:= y
CFLAGS				+= -DMYDLINK
CGIS				+= mydlink/form_login
CGIS				+= mydlink/form_logout
endif

#####################################################################
# Common libs
ifeq ($(strip $(REQUIRE_LIBUSOCK)),y)
CMLIB_SRCS += unixsock.c
endif
ifeq ($(strip $(REQUIRE_SLOOP)),y)
CMLIB_SRCS += sloop.c
endif
ifeq ($(strip $(REQUIRE_BASE64)),y)
CMLIB_SRCS += base64.c
endif
ifeq ($(strip $(REQUIRE_LIBIXML)),y)
CFLAGS += -I./ixml/inc
LDFLAGS += -L./ixml/src/obj -lixml
PREBUILD += libixml
endif
#####################################################################
# Directories.
ifeq ($(strip $(REQUIRE_DIR_USRSBIN)),y)
DIRS += $(TARGET)/usr/sbin
endif
ifeq ($(strip $(REQUIRE_DIR_WEB)),y)
DIRS += $(TARGET)/htdocs/web
endif
ifeq ($(strip $(REQUIRE_DIR_UPNP)),y)
DIRS += $(TARGET)/htdocs/upnp
endif
ifeq ($(strip $(CONFIG_CGIBIN_ARIES_HNAP)),y)
DIRS += $(TARGET)/htdocs/HNAP1
endif
ifeq ($(strip $(REQUIRE_DIR_MYDLINK)),y)
DIRS += $(TARGET)/htdocs/mydlink
endif
#####################################################################
-include $(TOPDIR)/Rules.mk
#####################################################################

showconfig:
	@echo "APPLET     = "$(APPLET)
	@echo "LOCAL_SRCS = "$(LOCAL_SRCS)
	@echo "CMLIB_SRCS = "$(CMLIB_SRCS)
	@echo "CFLAGS     = "$(CFLAGS)
	@echo "CMDS       = "$(CMDS)
	@echo "CGIS       = "$(CGIS)
	@echo "DIRS       = "$(DIRS)
	@echo "OTHER_TARGETS = "$(OTHER_TARGETS)

clean:
	@echo -e "\033[32mcleaning $(MYNAME) ...\033[0m"
	$(Q)make clean_objs V=$(V) DEBUG=$(DEBUG)
	$(Q)make -C ixml clean

libixml:
	@echo -e "\033[32mBuilding ixml library ...\033[0m"
	$(Q)make -C ixml

install:
	@echo -e "\033[32minstalling $(MYNAME) ...\033[0m"
	$(Q)for i in $(DIRS) ; do [ -d $$i ] || mkdir -p $$i ; done
	$(Q)[ -z "$(STRIP)" ] || $(STRIP) $(APPLET)
	$(Q)cp $(APPLET) $(TARGET)/htdocs
	$(Q)for i in $(CGIS) ; do ln -sf /htdocs/$(APPLET) $(TARGET)/htdocs/$$i ; done
	$(Q)for i in $(CMDS) ; do ln -sf /htdocs/$(APPLET) $(TARGET)/usr/sbin/$$i ; done

gpl:
	rm -f *.c *.o *.h .depend
	rm -rf ixml
	rm -rf scandir
	mv Makefile.gpl Makefile

.PHONY: showconfig clean install gpl
