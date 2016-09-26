%define webdir /var/www
%define legacy_name geni-ch

Name:           geni-portal
Version:        3.18
Release:        1%{?dist}
Summary:        GENI Experimenter Portal
BuildArch:      noarch
License:        GENI Public License
URL:            https://github.com/GENI-NSF/geni-portal
Source:         %{legacy_name}-%{version}.tar.gz
Group:          Applications/Internet
BuildRequires:  httpd, texinfo
Requires:       python-sqlalchemy, python-psycopg2, postgresql, httpd
Requires:       mod_ssl, shibboleth, php, php-pear, php-pgsql, php-xmlrpc
Requires:       geni-tools php-pear-MDB2 php-pear-MDB2-Driver-pgsql

%description
GENI Portal provides an web interface to the GENI Federation Services
and provides basic management of GENI slices.

%prep

# We pass the legacy name for the directory in which to CD
# If/when the tarball moves to geni-portal, remove the "-n DIRNAME" arg
%setup -q -n %{legacy_name}-%{version}

%build
%configure --with-apache-user=apache --with-apache-group=apache
make %{?_smp_mflags}

%install
rm -rf $RPM_BUILD_ROOT
%make_install
rm -f $RPM_BUILD_ROOT%{_infodir}/dir

%clean
rm -rf $RPM_BUILD_ROOT

%files
%defattr(-,root,root)
%doc %{_infodir}/geni-portal.info.gz
%doc %{_mandir}/man1/geni-fetch-aggmon.1.gz
%doc %{_mandir}/man1/geni-fetch-amdown.1.gz
%doc %{_mandir}/man1/geni-get-ad-rspecs.1.gz
%doc %{_mandir}/man1/geni-parse-map-data.1.gz
%doc %{_mandir}/man1/geni-portal-install-templates.1.gz
%doc %{_mandir}/man1/geni-sync-wireless.1.gz
%doc %{_mandir}/man1/geni-watch-omni.1.gz
%{_bindir}/geni-get-ad-rspecs
%{_bindir}/geni-manage-maintenance
%{_bindir}/geni-parse-map-data
%{_bindir}/geni-sync-wireless
%{_bindir}/geni-watch-omni
%{_datadir}/%{legacy_name}/ch/apache2.conf
%{_datadir}/%{legacy_name}/ch/www/cainfo.html
%{_datadir}/%{legacy_name}/ch/www/favicon.ico
%{_datadir}/%{legacy_name}/ch/www/index.html
%{_datadir}/%{legacy_name}/ch/www/salist.json
%{_datadir}/%{legacy_name}/km/db/postgresql/schema.sql
%{_datadir}/%{legacy_name}/km/db/postgresql/update-1.sql
%{_datadir}/%{legacy_name}/km/db/postgresql/update-2.sql
%{_datadir}/%{legacy_name}/lib/php/abac.php
%{_datadir}/%{legacy_name}/lib/php/aggstatus.php
%{_datadir}/%{legacy_name}/lib/php/am_client.php
%{_datadir}/%{legacy_name}/lib/php/am_map.php
%{_datadir}/%{legacy_name}/lib/php/cert_utils.php
%{_datadir}/%{legacy_name}/lib/php/chapi.php
%{_datadir}/%{legacy_name}/lib/php/client_utils.php
%{_datadir}/%{legacy_name}/lib/php/cs_client.php
%{_datadir}/%{legacy_name}/lib/php/cs_constants.php
%{_datadir}/%{legacy_name}/lib/php/db-util.php
%{_datadir}/%{legacy_name}/lib/php/db_utils.php
%{_datadir}/%{legacy_name}/lib/php/file_utils.php
%{_datadir}/%{legacy_name}/lib/php/footer.php
%{_datadir}/%{legacy_name}/lib/php/gemini_rspec_routines.php
%{_datadir}/%{legacy_name}/lib/php/geni_syslog.php
%{_datadir}/%{legacy_name}/lib/php/guard.php
%{_datadir}/%{legacy_name}/lib/php/header.php
%{_datadir}/%{legacy_name}/lib/php/irods_utils.php
%{_datadir}/%{legacy_name}/lib/php/jacks-app.php
%{_datadir}/%{legacy_name}/lib/php/jacks-editor-app.php
%{_datadir}/%{legacy_name}/lib/php/json_util.php
%{_datadir}/%{legacy_name}/lib/php/logging_client.php
%{_datadir}/%{legacy_name}/lib/php/logging_constants.php
%{_datadir}/%{legacy_name}/lib/php/ma_client.php
%{_datadir}/%{legacy_name}/lib/php/ma_constants.php
%{_datadir}/%{legacy_name}/lib/php/maintenance_mode.php
%{_datadir}/%{legacy_name}/lib/php/map.html
%{_datadir}/%{legacy_name}/lib/php/message_handler.php
%{_datadir}/%{legacy_name}/lib/php/omni_invocation_constants.php
%{_datadir}/%{legacy_name}/lib/php/pa_client.php
%{_datadir}/%{legacy_name}/lib/php/pa_constants.php
%{_datadir}/%{legacy_name}/lib/php/permission_manager.php
%{_datadir}/%{legacy_name}/lib/php/portal.php
%{_datadir}/%{legacy_name}/lib/php/print-text-helpers.php
%{_datadir}/%{legacy_name}/lib/php/proj_slice_member.php
%{_datadir}/%{legacy_name}/lib/php/query-details.php
%{_datadir}/%{legacy_name}/lib/php/query-sliverstatus.php
%{_datadir}/%{legacy_name}/lib/php/response_format.php
%{_datadir}/%{legacy_name}/lib/php/rq_client.php
%{_datadir}/%{legacy_name}/lib/php/rq_constants.php
%{_datadir}/%{legacy_name}/lib/php/rq_controller.php
%{_datadir}/%{legacy_name}/lib/php/rq_utils.php
%{_datadir}/%{legacy_name}/lib/php/sa_client.php
%{_datadir}/%{legacy_name}/lib/php/sa_constants.php
%{_datadir}/%{legacy_name}/lib/php/services.php
%{_datadir}/%{legacy_name}/lib/php/session_cache.php
%{_datadir}/%{legacy_name}/lib/php/settings.php
%{_datadir}/%{legacy_name}/lib/php/signer.php
%{_datadir}/%{legacy_name}/lib/php/sliceresource.js
%{_datadir}/%{legacy_name}/lib/php/smime.php
%{_datadir}/%{legacy_name}/lib/php/speaksforcred.php
%{_datadir}/%{legacy_name}/lib/php/sr_client.php
%{_datadir}/%{legacy_name}/lib/php/sr_constants.php
%{_datadir}/%{legacy_name}/lib/php/status_constants.php
%{_datadir}/%{legacy_name}/lib/php/tabs.js
%{_datadir}/%{legacy_name}/lib/php/tool-breadcrumbs.php
%{_datadir}/%{legacy_name}/lib/php/tool-expired-projects.php
%{_datadir}/%{legacy_name}/lib/php/tool-expired-slices.php
%{_datadir}/%{legacy_name}/lib/php/tool-lookupids.php
%{_datadir}/%{legacy_name}/lib/php/tool-projects.php
%{_datadir}/%{legacy_name}/lib/php/tool-rspec-parse.php
%{_datadir}/%{legacy_name}/lib/php/tool-rspecs.js
%{_datadir}/%{legacy_name}/lib/php/tool-rspecs.php
%{_datadir}/%{legacy_name}/lib/php/tool-showmessage.php
%{_datadir}/%{legacy_name}/lib/php/tool-slices.php
%{_datadir}/%{legacy_name}/lib/php/tools-admin.php
%{_datadir}/%{legacy_name}/lib/php/tools-user.php
%{_datadir}/%{legacy_name}/lib/php/uploadsshkey.html
%{_datadir}/%{legacy_name}/lib/php/user-preferences.php
%{_datadir}/%{legacy_name}/lib/php/user.php
%{_datadir}/%{legacy_name}/lib/php/util.php
%{_datadir}/%{legacy_name}/openid/apache2-consumer.conf
%{_datadir}/%{legacy_name}/openid/apache2.conf
%{_datadir}/%{legacy_name}/openid/Auth/OpenID.php
%{_datadir}/%{legacy_name}/openid/Auth/OpenID/Association.php
%{_datadir}/%{legacy_name}/openid/Auth/OpenID/AX.php
%{_datadir}/%{legacy_name}/openid/Auth/OpenID/BigMath.php
%{_datadir}/%{legacy_name}/openid/Auth/OpenID/Consumer.php
%{_datadir}/%{legacy_name}/openid/Auth/OpenID/CryptUtil.php
%{_datadir}/%{legacy_name}/openid/Auth/OpenID/DatabaseConnection.php
%{_datadir}/%{legacy_name}/openid/Auth/OpenID/DiffieHellman.php
%{_datadir}/%{legacy_name}/openid/Auth/OpenID/Discover.php
%{_datadir}/%{legacy_name}/openid/Auth/OpenID/DumbStore.php
%{_datadir}/%{legacy_name}/openid/Auth/OpenID/Extension.php
%{_datadir}/%{legacy_name}/openid/Auth/OpenID/FileStore.php
%{_datadir}/%{legacy_name}/openid/Auth/OpenID/HMAC.php
%{_datadir}/%{legacy_name}/openid/Auth/OpenID/Interface.php
%{_datadir}/%{legacy_name}/openid/Auth/OpenID/KVForm.php
%{_datadir}/%{legacy_name}/openid/Auth/OpenID/MDB2Store.php
%{_datadir}/%{legacy_name}/openid/Auth/OpenID/MemcachedStore.php
%{_datadir}/%{legacy_name}/openid/Auth/OpenID/Message.php
%{_datadir}/%{legacy_name}/openid/Auth/OpenID/MySQLStore.php
%{_datadir}/%{legacy_name}/openid/Auth/OpenID/Nonce.php
%{_datadir}/%{legacy_name}/openid/Auth/OpenID/PAPE.php
%{_datadir}/%{legacy_name}/openid/Auth/OpenID/Parse.php
%{_datadir}/%{legacy_name}/openid/Auth/OpenID/PostgreSQLStore.php
%{_datadir}/%{legacy_name}/openid/Auth/OpenID/Server.php
%{_datadir}/%{legacy_name}/openid/Auth/OpenID/ServerRequest.php
%{_datadir}/%{legacy_name}/openid/Auth/OpenID/SQLiteStore.php
%{_datadir}/%{legacy_name}/openid/Auth/OpenID/SQLStore.php
%{_datadir}/%{legacy_name}/openid/Auth/OpenID/SReg.php
%{_datadir}/%{legacy_name}/openid/Auth/OpenID/TrustRoot.php
%{_datadir}/%{legacy_name}/openid/Auth/OpenID/URINorm.php
%{_datadir}/%{legacy_name}/openid/Auth/Yadis/HTTPFetcher.php
%{_datadir}/%{legacy_name}/openid/Auth/Yadis/Manager.php
%{_datadir}/%{legacy_name}/openid/Auth/Yadis/Misc.php
%{_datadir}/%{legacy_name}/openid/Auth/Yadis/ParanoidHTTPFetcher.php
%{_datadir}/%{legacy_name}/openid/Auth/Yadis/ParseHTML.php
%{_datadir}/%{legacy_name}/openid/Auth/Yadis/PlainHTTPFetcher.php
%{_datadir}/%{legacy_name}/openid/Auth/Yadis/XML.php
%{_datadir}/%{legacy_name}/openid/Auth/Yadis/XRDS.php
%{_datadir}/%{legacy_name}/openid/Auth/Yadis/XRI.php
%{_datadir}/%{legacy_name}/openid/Auth/Yadis/XRIRes.php
%{_datadir}/%{legacy_name}/openid/Auth/Yadis/Yadis.php
%{_datadir}/%{legacy_name}/openid/src/consumer/common.php
%{_datadir}/%{legacy_name}/openid/src/consumer/finish_auth.php
%{_datadir}/%{legacy_name}/openid/src/consumer/index.php
%{_datadir}/%{legacy_name}/openid/src/consumer/try_auth.php
%{_datadir}/%{legacy_name}/openid/src/server-direct/config.php
%{_datadir}/%{legacy_name}/openid/src/server-direct/index.php
%{_datadir}/%{legacy_name}/openid/src/server-direct/lib/actions.php
%{_datadir}/%{legacy_name}/openid/src/server-direct/lib/common.php
%{_datadir}/%{legacy_name}/openid/src/server-direct/lib/render.php
%{_datadir}/%{legacy_name}/openid/src/server-direct/lib/render/about.php
%{_datadir}/%{legacy_name}/openid/src/server-direct/lib/render/idpage.php
%{_datadir}/%{legacy_name}/openid/src/server-direct/lib/render/idpXrds.php
%{_datadir}/%{legacy_name}/openid/src/server-direct/lib/render/login.php
%{_datadir}/%{legacy_name}/openid/src/server-direct/lib/render/trust.php
%{_datadir}/%{legacy_name}/openid/src/server-direct/lib/render/userXrds.php
%{_datadir}/%{legacy_name}/openid/src/server-direct/lib/session.php
%{_datadir}/%{legacy_name}/openid/src/server-direct/openid-server.css
%{_datadir}/%{legacy_name}/openid/src/server-direct/server.php
%{_datadir}/%{legacy_name}/openid/src/server-direct/setup.php
%{_datadir}/%{legacy_name}/openid/src/server-indirect/config.php
%{_datadir}/%{legacy_name}/openid/src/server-indirect/index.php
%{_datadir}/%{legacy_name}/openid/src/server-indirect/kmactivate.php
%{_datadir}/%{legacy_name}/openid/src/server-indirect/lib/actions.php
%{_datadir}/%{legacy_name}/openid/src/server-indirect/lib/common.php
%{_datadir}/%{legacy_name}/openid/src/server-indirect/lib/render.php
%{_datadir}/%{legacy_name}/openid/src/server-indirect/lib/render/about.php
%{_datadir}/%{legacy_name}/openid/src/server-indirect/lib/render/idpage.php
%{_datadir}/%{legacy_name}/openid/src/server-indirect/lib/render/idpXrds.php
%{_datadir}/%{legacy_name}/openid/src/server-indirect/lib/render/login.php
%{_datadir}/%{legacy_name}/openid/src/server-indirect/lib/render/trust.php
%{_datadir}/%{legacy_name}/openid/src/server-indirect/lib/render/userXrds.php
%{_datadir}/%{legacy_name}/openid/src/server-indirect/lib/session.php
%{_datadir}/%{legacy_name}/openid/src/server-indirect/openid-server.css
%{_datadir}/%{legacy_name}/openid/src/server-indirect/server.php
%{_datadir}/%{legacy_name}/openid/src/server-indirect/setup.php
%{_datadir}/%{legacy_name}/portal/apache2-http.conf
%{_datadir}/%{legacy_name}/portal/db/postgresql/data.sql
%{_datadir}/%{legacy_name}/portal/db/postgresql/schema.sql
%{_datadir}/%{legacy_name}/portal/db/postgresql/update-1.sql
%{_datadir}/%{legacy_name}/portal/db/postgresql/update-10.sql
%{_datadir}/%{legacy_name}/portal/db/postgresql/update-11.sql
%{_datadir}/%{legacy_name}/portal/db/postgresql/update-12.sql
%{_datadir}/%{legacy_name}/portal/db/postgresql/update-2.sql
%{_datadir}/%{legacy_name}/portal/db/postgresql/update-3.sql
%{_datadir}/%{legacy_name}/portal/db/postgresql/update-4.sql
%{_datadir}/%{legacy_name}/portal/db/postgresql/update-5.sql
%{_datadir}/%{legacy_name}/portal/db/postgresql/update-6.sql
%{_datadir}/%{legacy_name}/portal/db/postgresql/update-7.sql
%{_datadir}/%{legacy_name}/portal/db/postgresql/update-8.sql
%{_datadir}/%{legacy_name}/portal/db/postgresql/update-9.sql
%{_datadir}/%{legacy_name}/portal/gcf.d/example-gcf.ini
%{_datadir}/%{legacy_name}/portal/gcf/src/logging.conf
%{_datadir}/%{legacy_name}/portal/gcf/src/omni_php.py
%{_datadir}/%{legacy_name}/portal/gcf/src/omni_php.pyc
%{_datadir}/%{legacy_name}/portal/gcf/src/omni_php.pyo
%{_datadir}/%{legacy_name}/portal/gcf/src/stitcher_logging_template.conf
%{_datadir}/%{legacy_name}/portal/gcf/src/stitcher_php.py
%{_datadir}/%{legacy_name}/portal/gcf/src/stitcher_php.pyc
%{_datadir}/%{legacy_name}/portal/gcf/src/stitcher_php.pyo
%{_datadir}/%{legacy_name}/sr/certs/genidesktop.netlab.uky.edu.pem
%{_datadir}/%{legacy_name}/templates/idp-metadata-extension.xml.tmpl
%{_datadir}/%{legacy_name}/templates/install-sp-centos.sh.tmpl
%{_datadir}/%{legacy_name}/templates/parameters.json
%{_datadir}/%{legacy_name}/templates/portal-ssl.conf.tmpl
%{_datadir}/%{legacy_name}/templates/settings.php.tmpl
%{_datadir}/%{legacy_name}/templates/shibboleth2.xml.tmpl
%{_datadir}/%{legacy_name}/templates/templates.json
%{_sbindir}/gen-add-constraints.sql
%{_sbindir}/gen-drop-constraints.sql
%{_sbindir}/geni-fetch-aggmon
%{_sbindir}/geni-fetch-amdown
%{_sbindir}/geni-portal-install-templates
%{_sbindir}/import_database.py
%{_sbindir}/portal-backup
%{_sbindir}/renew-certs
%{_sbindir}/update_user_certs.py
%{_sysconfdir}/%{legacy_name}/ch-services.ini
%{_sysconfdir}/%{legacy_name}/example-services.ini
%{_sysconfdir}/%{legacy_name}/example-settings.php
%{_sysconfdir}/%{legacy_name}/member-id-columns.dat
%{python_sitelib}/portal_utils/__init__.py
%{python_sitelib}/portal_utils/__init__.pyc
%{python_sitelib}/portal_utils/__init__.pyo
%{python_sitelib}/portal_utils/orbit_interface.py
%{python_sitelib}/portal_utils/orbit_interface.pyc
%{python_sitelib}/portal_utils/orbit_interface.pyo
%{webdir}/amstatus.php
%{webdir}/common/css/kmtool.css
%{webdir}/common/css/mobile-portal.css
%{webdir}/common/css/newportal.css
%{webdir}/common/css/portal.css
%{webdir}/common/dots.gif
%{webdir}/common/map/current.json
%{webdir}/common/map/gmap3.js
%{webdir}/common/nsf1.gif
%{webdir}/common/topbar_gradient.png
%{webdir}/common/topbar_gradient2.png
%{webdir}/favicon.ico
%{webdir}/images/EG-VM-noTxt-centered.svg
%{webdir}/images/EG-VM-noTxt.svg
%{webdir}/images/EG-VM.svg
%{webdir}/images/geni-header-left.png
%{webdir}/images/geni-header-right.png
%{webdir}/images/geni.png
%{webdir}/images/geni_globe.png
%{webdir}/images/geni_globe_small.png
%{webdir}/images/header-home.jpg
%{webdir}/images/menu.png
%{webdir}/images/openVZvm-noTxt-centered.svg
%{webdir}/images/openVZvm-noTxt.svg
%{webdir}/images/openVZvm.svg
%{webdir}/images/orbit_banner.png
%{webdir}/images/pin.png
%{webdir}/images/portal.png
%{webdir}/images/portal2.png
%{webdir}/images/RawPC-EG-noTxt-centered.svg
%{webdir}/images/RawPC-EG-noTxt.svg
%{webdir}/images/RawPC-EG.svg
%{webdir}/images/RawPC-IG-noTxt-centered.svg
%{webdir}/images/RawPC-IG-noTxt.svg
%{webdir}/images/RawPC-IG.svg
%{webdir}/images/router.svg
%{webdir}/images/site-icon.png
%{webdir}/images/staticmap.png
%{webdir}/images/Symbols-Tips-icon-clear.png
%{webdir}/images/UseGENI.png
%{webdir}/images/witest-logo-white.png
%{webdir}/images/Xen-VM-noTxt-centered.svg
%{webdir}/images/Xen-VM-noTxt.svg
%{webdir}/images/Xen-VM.svg
%{webdir}/index.php
%{webdir}/login-help.php
%{webdir}/policy/privacy.html
%{webdir}/secure/accept-project-invite.php
%{webdir}/secure/admin.php
%{webdir}/secure/aggregates.php
%{webdir}/secure/amdetails.php
%{webdir}/secure/amstatus.js
%{webdir}/secure/amstatus.php
%{webdir}/secure/ask-for-project.php
%{webdir}/secure/cancel-join-project.php
%{webdir}/secure/cards.js
%{webdir}/secure/certificate.php
%{webdir}/secure/confirm-sliverdelete.php
%{webdir}/secure/contact-us.php
%{webdir}/secure/createimage.php
%{webdir}/secure/createslice.php
%{webdir}/secure/createsliver.php
%{webdir}/secure/dashboard.js
%{webdir}/secure/dashboard.php
%{webdir}/secure/db_error_test.php
%{webdir}/secure/debug_clearcache.php
%{webdir}/secure/deletesliver.php
%{webdir}/secure/deletesshkey.php
%{webdir}/secure/disable-slice.php
%{webdir}/secure/do-accept-project-invite.php
%{webdir}/secure/do-delete-project-member.php
%{webdir}/secure/do-disable-slice.php
%{webdir}/secure/do-downloadputtykey.php
%{webdir}/secure/do-edit-project-member.php
%{webdir}/secure/do-edit-project-membership.php
%{webdir}/secure/do-edit-project.php
%{webdir}/secure/do-edit-slice-member.php
%{webdir}/secure/do-edit-slice.php
%{webdir}/secure/do-get-logs.php
%{webdir}/secure/do-handle-lead-request.php
%{webdir}/secure/do-handle-project-request.php
%{webdir}/secure/do-modify.php
%{webdir}/secure/do-register.php
%{webdir}/secure/do-renew-slice.php
%{webdir}/secure/do-renew.php
%{webdir}/secure/do-slice-search.php
%{webdir}/secure/do-update-user-preferences.php
%{webdir}/secure/do-update-keys.php
%{webdir}/secure/do-upload-project-members.php
%{webdir}/secure/do-user-admin.php
%{webdir}/secure/do-user-search.php
%{webdir}/secure/dologout.php
%{webdir}/secure/downloadkeycert.php
%{webdir}/secure/downloadomnibundle.php
%{webdir}/secure/downloadputtykey.php
%{webdir}/secure/downloadsshkey.php
%{webdir}/secure/downloadsshpublickey.php
%{webdir}/secure/edit-project-member.php
%{webdir}/secure/edit-project.php
%{webdir}/secure/edit-slice-member.php
%{webdir}/secure/edit-slice.php
%{webdir}/secure/env.php
%{webdir}/secure/error-text.php
%{webdir}/secure/future.json
%{webdir}/secure/gemini.php
%{webdir}/secure/gemini_add_global_node.php
%{webdir}/secure/generatesshkey.php
%{webdir}/secure/get_omni_invocation_data.php
%{webdir}/secure/getversion.php
%{webdir}/secure/handle-project-request.php
%{webdir}/secure/help.php
%{webdir}/secure/home.php
%{webdir}/secure/image_operations.php
%{webdir}/secure/index.php
%{webdir}/secure/invite-to-geni.php
%{webdir}/secure/invite-to-project.php
%{webdir}/secure/irods.php
%{webdir}/secure/jacks-app-details.php
%{webdir}/secure/jacks-app-expanded.php
%{webdir}/secure/jacks-app-reserve.php
%{webdir}/secure/jacks-app-status.php
%{webdir}/secure/jacks-app.css
%{webdir}/secure/jacks-app.js
%{webdir}/secure/jacks-editor-app-expanded.php
%{webdir}/secure/jacks-editor-app.css
%{webdir}/secure/jacks-editor-app.js
%{webdir}/secure/jacks-lib.js
%{webdir}/secure/jfed.php
%{webdir}/secure/join-project.js
%{webdir}/secure/join-project.php
%{webdir}/secure/join-this-project.php
%{webdir}/secure/km_utils.php
%{webdir}/secure/kmactivate.php
%{webdir}/secure/kmcert.php
%{webdir}/secure/kmfooter.php
%{webdir}/secure/kmheader.php
%{webdir}/secure/kmhome.php
%{webdir}/secure/kmnoemail.php
%{webdir}/secure/kmsendemail.php
%{webdir}/secure/kmconfirmemail.php
%{webdir}/secure/listresources.php
%{webdir}/secure/lookup-project.php
%{webdir}/secure/listresources_plain.php
%{webdir}/secure/loadcert.js
%{webdir}/secure/loadcert.php
%{webdir}/secure/maintenance_redirect_page.php
%{webdir}/secure/modify.php
%{webdir}/secure/omni-bundle.php
%{webdir}/secure/permission_manager_test.php
%{webdir}/secure/portal-jacks-app.js
%{webdir}/secure/portal-jacks-editor-app.js
%{webdir}/secure/portal_omni_config.php
%{webdir}/secure/preferences.php
%{webdir}/secure/print-text.php
%{webdir}/secure/profile.php
%{webdir}/secure/project-member.php
%{webdir}/secure/project.php
%{webdir}/secure/projects.php
%{webdir}/secure/raw-sliverstatus.php
%{webdir}/secure/renewcert.php
%{webdir}/secure/renewsliver.php
%{webdir}/secure/request_test.php
%{webdir}/secure/restartsliver.php
%{webdir}/secure/rspecdelete.php
%{webdir}/secure/rspecdownload.php
%{webdir}/secure/rspecs.php
%{webdir}/secure/rspecupdate.php
%{webdir}/secure/rspecupload.php
%{webdir}/secure/rspecuploadparser.php
%{webdir}/secure/rspecview.php
%{webdir}/secure/saverspectoserver.php
%{webdir}/secure/savi.php
%{webdir}/secure/selectrspec.html
%{webdir}/secure/send_bug_report.php
%{webdir}/secure/slice-add-resources-jacks.css
%{webdir}/secure/slice-add-resources-jacks.js
%{webdir}/secure/slice-add-resources-jacks.php
%{webdir}/secure/slice-add-resources.js
%{webdir}/secure/slice-add-resources.php
%{webdir}/secure/slice-jacks.css
%{webdir}/secure/slice-map-data.php
%{webdir}/secure/slice-map-view.php
%{webdir}/secure/slice-member.php
%{webdir}/secure/slice-table.css
%{webdir}/secure/slice.js
%{webdir}/secure/slice.php
%{webdir}/secure/slicecred.php
%{webdir}/secure/sliceresource.php
%{webdir}/secure/slices.php
%{webdir}/secure/sliverdelete.php
%{webdir}/secure/speaks-for-delete.php
%{webdir}/secure/speaks-for-upload.php
%{webdir}/secure/speaks-for.css
%{webdir}/secure/speaks-for.js
%{webdir}/secure/speaks-for.php
%{webdir}/secure/sshkeyedit.php
%{webdir}/secure/status_constants_import.php
%{webdir}/secure/tool-aggwarning.php
%{webdir}/secure/tool-omniconfig.php
%{webdir}/secure/tool-slices.js
%{webdir}/secure/tools-user.js
%{webdir}/secure/updatekeys.js
%{webdir}/secure/updatekeys.php
%{webdir}/secure/upload-file.php
%{webdir}/secure/upload-project-members.php
%{webdir}/secure/uploadsshkey.php
%{webdir}/secure/wimax-enable.php
%{webdir}/secure/wireless_operations.php
%{webdir}/secure/wireless_redirect.php

%changelog
* Wed Oct 28 2015 Tom Mitchell <tmitchell@bbn.com> - 3.7-1%{?dist}
- Initial RPM packaging
