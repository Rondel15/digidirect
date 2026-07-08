1.0.0
=============
* Solution Architecture:
    * [#143087](https://digidirect.tpondemand.com/entity/143087) -- Abstract Integration. Solution Architecture
* New features:
    * [#143090](https://digidirect.tpondemand.com/entity/143090) -- Configuration
    * [#138943](https://digidirect.tpondemand.com/entity/138943) -- As admin user i want to have grid with all created integration processes which extended from Abstract integration engine in admin panel
    * [#138949](https://digidirect.tpondemand.com/entity/138949) -- As admin user i want to have ability to set up chain process for some processes in process grid
    * [#138977](https://digidirect.tpondemand.com/entity/138977) -- As admin user i want to have ability to reset status of integration from processes grid
    * [#138946](https://digidirect.tpondemand.com/entity/138946) -- As admin user i want to have ability to run integrations from process status popup immediately using run options if it required
    * [#138948](https://digidirect.tpondemand.com/entity/138948) -- As admin user i want to have ability to run integration using separate server process (next cron execution) and using run options if it required
    * [#138981](https://digidirect.tpondemand.com/entity/138981) -- As Server user I want to be able to run integration process from CLI
    * [#138947](https://digidirect.tpondemand.com/entity/138947) -- As admin user i want to check run status / log during integration executing
    * [#138978](https://digidirect.tpondemand.com/entity/138978) -- As developer i want to have ability to use unified logging system for all integration processes and different types of logs
    * [#138979](https://digidirect.tpondemand.com/entity/138979) -- As developer i want to have ability to use unified backup system for all integration processes
    * [#138983](https://digidirect.tpondemand.com/entity/138983) -- As admin user i want to have log grid in admin panel
    * [#138984](https://digidirect.tpondemand.com/entity/138984) -- As admin user i want to have ability to see details of each process run with full description
    * [#138985](https://digidirect.tpondemand.com/entity/138985) -- As admin user i want to have ability to download log file of each process run and have links for downloading integrations files if they were created / uploaded during execution
    * [#138986](https://digidirect.tpondemand.com/entity/138986) -- As admin user i want to have ability to send log details / log file on emails addresses entered in log description popup
    * [#143089](https://digidirect.tpondemand.com/entity/143089) -- As admin i want to see queue grid of processes and have ability to run some of processes from this grid
    * [#148429](https://digidirect.tpondemand.com/entity/148429) -- As developer, I want to have ability to create custom rules for integration process

1.1.0
=============
* New features:
    * [#153437](https://digidirect.tpondemand.com/entity/153437) -- ENABLE PROCESS. As an admin, I want to be able to enable / disable abstract integration interface in backend (see description)

1.2.0
=============
* New features:
    * [#169310](https://digidirect.tpondemand.com/entity/169310) -- Product Import Library
    * [#178008](https://digidirect.tpondemand.com/entity/178008) -- Category Import Library

1.2.1
=============
* Improvements:
    * Valitron Validator. Ability to use an optional argument for array validation

1.3.0
=============
* New features:
    * [#179591](https://digidirect.tpondemand.com/entity/179591) -- Abstract Integration. Mapper
    * [#179593](https://digidirect.tpondemand.com/entity/179593) -- Abstract Integration. Mapper. Solution Architecture
    * [#179594](https://digidirect.tpondemand.com/entity/179594) -- As a system, I want abstract integration mapper to be created

1.4.0
=============
* New features:
    * [#169311](https://digidirect.tpondemand.com/entity/169311) -- Abstract Integration. Customer Library
    * [#169317](https://digidirect.tpondemand.com/entity/169317) -- Abstract Integration. Customer Library. Solution Architecture
    * [#177319](https://digidirect.tpondemand.com/entity/177319) -- Customer Address
    * [#177320](https://digidirect.tpondemand.com/entity/177320) -- Customer Personal Information

1.5.0
=============
* New features:
    * [#198526](https://digidirect.tpondemand.com/entity/198526) -- As an admin I want to set up 'cron' running time for each integration

1.6.0
=============
* New features:
    * [#192734](https://digidirect.tpondemand.com/entity/192734) -- Coupons. Connection and Default Process /Default Mapping
    * [#198739](https://digidirect.tpondemand.com/entity/198739) -- Inventory import

1.7.0
=============
* New features:
    * [#199456](https://digidirect.tpondemand.com/entity/199456) -- Reward Points Library

1.8.0
=============
* Improvements:
    * Compatibility with Magento 2.2
    * Email Logger added

1.9.0
=============
* New features:
    * [#208318](https://digidirect.tpondemand.com/entity/208318) -- Abstract Integration. Catalog Price Rules Library
* Bugfixes:
    * [#215803](https://digidirect.tpondemand.com/entity/215803) -- 504 page is opened instead of eWave > Processes page in the back-office

1.10.0
=============
* New features:
    * [#138979](https://digidirect.tpondemand.com/entity/138979) -- As developer i want to have ability to use unified backup system for all integration processes
    * [#216288](https://digidirect.tpondemand.com/entity/216288) -- As an admin, I want to have an ability for a special character to be converted automatically
* Bugfixes:
    * [#217186](https://digidirect.tpondemand.com/entity/217186) -- Interfaces are not triggered by cron schedule
    * [#218699](https://digidirect.tpondemand.com/entity/218699) -- Error messages on uploading enrichment file are not informative

1.11.0
=============
* Removed:
    * Digidirect\AI\Model\Lib\Connector\AbstractCurlClient
    * Digidirect\AI\Model\Lib\Connector\CurlTypes\XmlClient
    * Digidirect\AI\Model\Lib\Connector\ConnectionInterface

* Added:
    * Digidirect\AI\Model\Lib\Connector\CurlHttpClient
    * Digidirect\AI\Model\Lib\Connector\CurlHttpClient\RestApi
    * Digidirect\AI\Model\Lib\Mapping\MapperTemplateFilter

* Bugfixes:
    * [#223021](https://digidirect.tpondemand.com/entity/223021) -- Log in request is too slow, no index is applied in query

1.12.0
=============
* Improvements:
    * Stock sku selection
    * Added date time to log files

1.13.0
=============
* Improvements:
    * Engine, Queue and Schedule refactoring, bugfixes.
    * Mapper template filter: improvements, bugfixes.
    * Stuck Order (for inventory imports): table, interface, classes.

1.13.1
=============
* Bugfixes:
    * ProcessorAbstract: removed "implements ProcessorInterface" because of old modules

1.13.2
=============
* Bugfixes:
    * Logger type File: changed file name format.

1.13.3
=============
* Improvements:
    * Processor Abstract now has properties to detect how process was executed
        * protected $isCronRun;
        * protected $isQueueRun;
        * protected $isScheduleRun;
* Bugfixes:
    * fixed bug in Curl Http Client when response body is empty.
    * fixed bug in Logs grid: date now stored in UTC+0 and displayed according to Timezone from settings.
    * [#228583](https://digidirect.tpondemand.com/entity/228583) -- Fixed bug: whole bunch now is not skipped if there were not valid items.

1.13.4
=============
* Improvements:
    * Logger: beautified logs: we log context not as json, we log it like var_export.
    * AI run options logging: we log them like var_export.
    * CURL Http Client: logs for database are cut from now(request body and response body).
    * product import: added behavior for categories (independent from product import behavior)
    * some improvements in mapper template filter.
* Bugfixes:
    * fixed Product Import: when attribute is imported with empty value, value of attribute in magento should be updated
    * fixed Mapper default value: now default value for nonexistent field is null
    * fixed code in AI Process Exception: there was override to 0.
    * fixed problem with reindex and deadlock.
    * fixed bug: process_code was not is unique on database level in integrations table.
    * some bugfixes for mapper template filter.

1.13.5
=============
* Improvements:
    * Schedule:
        * Removed 'scheduled' run option for integrations that were executed after scheduled run that was added by admin.
        * Db table `digidirect_ai_integrations`: added unique index in database for process_code + run_options.
        * Db table `digidirect_ai_integrations`: added column 'is_added_by_admin'.
    * Processor Abstract:
        * added property $isAdminRun to detect how process was executed. If it is added by admin scheduled run then $isAdminRun also will be true.    
* Bugfixes:
    * Schedule:
       * fixed bug when admin adds new scheduled run - there was override of any existing scheduled run even if run options are not the same.
       * fixed bug with schedule repository: getList method.
    * run process from shell command: added 'hack' to avoid error 'Area code is not set' - set AREA_GLOBAL if not set.
    * deployment: fixed bug: deployment could be run without connection to db, but we used it. added check.
    * reindex after process: [#239387](https://digidirect.tpondemand.com/entity/239387) -- Indexers are locked after getProducts run.

1.13.6
=============
* Improvements:
    * Cronjobs `ai_run_by_schedule` and `ai_run_queue` were moved to group `digidirect_ai`.
* Bugfixes:
    * Settings: all settings declared as global but there were tries to get them from website scope.
    * default settings for logs now works: log backup days and email template.
    * popup to run integration (admin area) does not appear if there is no button "Run".

1.13.7
=============
* Improvements:
    * Logger. Type Db. Speed-Up.

1.13.8
=============
* Improvements:
    * Logger. Type Db. Added cron job for remove old entries. There is a setting in admin area. You can set X days. Or empty for disable cleanup.
    * Logger. Changed details "wrapper". Now it is a little shorter. 
    * Logger. Type Db. Speed-Up.
    * Product Import. Fixed bug for product category position overriding to 1.

1.13.9
=============
* Improvements:
    * Logger. Type file. Backup. Now files are stored in YYYY-MM-DD directory. It simplifies (speed ups) files search and directories scan.

1.14.0
=============
* Improvements:
    * Logger:
        * removed column 'id' from table 'digidirect_ai_logs_details'
        * column 'date' was renamed to 'create_at'
        * added column 'updated_at'
        * added column identifying_params to log entity to store process identifiers for search in grid
        * comment includes an error text from now (Engine adds it). Also it output uses multiple rows (nl2br).

1.14.1
=============
* Improvements:
    * Logger: Type file. Backup. Now archive .tar.gz is created instead of .tar.

1.14.2
=============
* Bugfixes:
    * [#265206](https://digidirect.tpondemand.com/entity/265206) -- Digidirect AI queue adds duplicated values in the queue
    * Integration Engine catches Fatal errors too (catch \Throwable instead of \Exception). fixed in [#265206](https://digidirect.tpondemand.com/entity/265206)

1.14.3
=============
* Bugfixes:
    * [#268698](https://digidirect.tpondemand.com/entity/268698) -- digidirect_ai_schedule_run - changed column id type from smallint to int.

1.15.0
=============
* New features:
    * [#277253](https://digidirect.tpondemand.com/entity/277253) -- [RUN OPTIONS] As an admin, I want to see run options in AI fail queue email
* Bugfixes:
    * fixed bugs for queue fail email / integration run email

1.15.1
=============
* Improvements:
    * admin run. fixed bug. if process was in processing and it has "miltiple_run" = 0, we have seen "View State" instead of "Run" button. So, admin could not execute process while other processes with the same process code are in running.
    * mapper. mapper template filter. speed up for case when we use too much times the same mapper - multiple entities.
    * queue. cron. select from database: small speed-up.
    * queue. default interval between re-tries now is 900 sec (15min) instead of 5 sec.
    * queue. processing. added support for QueueDependsException.
    * queue. digidirect_ai_queue_log table.removed column entity_id. added primary key for "log_id". removed unq idx for log_id + queue_id.
    * logs. grid. perfomance fix. do not join to queue_log table if there is no filter by queue_id. [#277300](https://digidirect.tpondemand.com/entity/277300)
    * logs. logging. fixed root case of infinite recursion in some cases of logging usage.
    * logs. logging. error handling. log full error text instead of stacktrace only ($e->__toString() instead of $e->getTraceAsString())

1.15.2
=============
* Improvements:
    * queue. added setting `Generate Run On Error Every (for Pending Depends)`. for queue depends default interval is 900s, for other cases default interval from now is 300s.
    * queue. added setting `Number of Runs On Error (Per Integration Process)`. it will override setting `Number of Runs On Error` if specified for process.
    * queue. added method getActiveQueueItemIdByProcessCodeAndProcessData.

1.15.3
=============
* Bugfixes:
    * [#293198](https://digidirect.tpondemand.com/entity/293198) -- Product Import. Label is not imported for the image with 'Alternative' role

1.15.4
=============
* Bugfixes:
    * [#300494](https://digidirect.tpondemand.com/entity/300494) -- RuntimeException during di:compile: Class Magento\CatalogImportExport\Model\Import\Product\MediaGalleryProcessor does not exist in [Digidirect\AI\Preferences\Model\Import\Product\Interceptor]

1.15.5
=============
* Bugfixes:
    * [#295240](https://digidirect.tpondemand.com/entity/295240) -- [Magento 2.3] Email can not be sent with attached Log-file

1.15.6
=============
* Bugfixes:
    * [#302709](https://digidirect.tpondemand.com/entity/302709) -- Product Import. Undefined index error. Images/Video import problem. Resolved for 2.2 & 2.3 versions. Video is working.

1.15.7
=============
* Refactoring:
    * Preferences/Model/Import/Product/CategoryProcessor: some private methods declared as protected to override in add-on.
* Bugfixes:
    * [#306537](https://digidirect.tpondemand.com/entity/306537) -- Product import. Product attributes are not set while using Magento Import functionality
    * [#306766](https://digidirect.tpondemand.com/entity/306766) -- Product import. Row "XXX" skipped. Error appear: Notice: Undefined index: rowNum

1.15.8
=============
* Bugfixes:
    * [#307093](https://digidirect.tpondemand.com/entity/307093) -- Product import overwrites URL key of the product to empty value
