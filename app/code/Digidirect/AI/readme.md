Abstract Integration
=====================

[wiki link](https://wiki.digidirect.com/display/LEGO/Abstract+Integration)

###Description

Extension can be used as a basis of Integration Engine, Lib function, Integrations logger function, Integration exceptions function.
Every integration process should be created based on AI extension and follow architecture of this extension. 

###VERSION 1.0.0
1. Engine function.
2. Lib Function.
3. Exception function
4. Log Function 
5. Run Queue

###VERSION 1.1.0
1. Ability to enable/disable processes

###VERSION 1.2.0
1. Product Import Library
2. Category Import Library

###VERSION 1.2.1
1. Valitron Validator. Ability to use an optional argument for array validation

###VERSION 1.3.0
1. Abstract Integration. Mapper

###VERSION 1.4.0
1. Abstract Integration. Customer Library

###VERSION 1.5.0
1. As an admin I want to set up 'cron' running time for each integration

### VERSION 1.6.0 
1. Added stock import as separate process
2. Gift Card Account import

### VERSION 1.7.0
1. Reward Points Library

### VERSION 1.8.0
1. Compatibility with Magento 2.2
2. Email Logger added

### VERSION 1.9.0
1. Catalog Price Rules Library

### VERSION 1.10.0
1. Extend Enrichment File Format
Bootstrap::INIT_PARAM_FILESYSTEM_DRIVERS – file system driver. It should be 
['file' => '\Digidirect\AI\Framework\Filesystem\Driver\File'].
The parameter you can specify right before loader creating or in .htaccess file by setEnv.

### VERSION 1.11.0
- Removed old connector classes and interface.
- Added new mapper class Digidirect\AI\Model\Lib\Mapping\MapperTemplateFilter. [see US](https://digidirect.tpondemand.com/entity/179593)
- Added new Curl Http Client and Rest Api client. 

### VERSION 1.12.0
- Stock sku selection
- Added date time to log files

### VERSION 1.13.0
- Engine, Queue and Schedule refactoring, bugfixes.
- Mapper template filter: improvements, bugfixes.
- Stuck Order (for inventory imports): table, interface, classes. 

### VERSION 1.13.1
- ProcessorAbstract: removed "implements ProcessorInterface" because of old modules

### VERSION 1.13.2
- Logger type File: changed file name format.

### VERSION 1.13.3
- Processor Abstract now has properties to detect how process was executed: $isCronRun, $isQueueRun, $isScheduleRun
- Bugfixes. See changelog.

### VERSION 1.13.4
- Logging changes. See changelog.
- Bugfixes. See changelog.

### VERSION 1.13.5
- digidirect_integrations.xml validation: chain_proccessor_code, can_admin_run, cron_time, state now are not required.
- Scheduled run and schedule refactoring.
- Removed run option 'scheduled' for scheduled run.
- Processor Abstract now has property $isAdminRun.
- Run process from shell command: added 'hack' to avoid error 'Area code is not set' - set AREA_GLOBAL if not set.
- Bugfixes. See changelog.

### VERSION 1.13.6
- All settings were declared as global and now we get them using default scope (not website).
- Cronjobs `ai_run_by_schedule` and `ai_run_queue` were moved to group `digidirect_ai`

### VERSION 1.13.7
- Logger. Type Db. Speed-Up.

### VERSION 1.13.8
- Logger. Type Db. Added cron job for removing old entries. There is a setting in admin area. You can set X days. Or empty for disable cleanup.
- Logger. Changed details "wrapper". Now it is a little shorter.
- Logger. Type Db. Speed-Up.
- Product Import. Fixed bug for product category position overriding to 1.

### VERSION 1.13.9
- Logger. Type file. Backup. Now files are stored in YYYY-MM-DD directory. It simplifies (speed ups) files search and directories scan.

### VERSION 1.14.0
- Logger. Removed column 'id' from table 'digidirect_ai_logs_details'.
- Logger. Column 'date' was renamed to 'create_at'.
- Logger. added column 'updated_at'.
- Logger. added column identifying_params to log entity to store process identifiers for search in grid.
- Logger. Comment includes an error text from now (Engine adds it). Also it output uses multiple rows (nl2br).
- Logger. Admin area grid. Watch Details -> See Details.

### VERSION 1.14.1
- Logger. Type file. Backup. Now archive .tar.gz is created instead of .tar.

### VERSION 1.14.2
- Bugfix. Do not create duplicate queue items.
- Bugfix. Integration Engine catches Fatal errors too

### VERSION 1.14.3
- Bugfix. Changed column id type from smallint to int.

### VERSION 1.15.0
- Queue fail email: added settings for emails list and email template. added run options to the email.
- Log email & Queue email. fixed bugs.

### VERSION 1.15.1
- admin run. fixed bug. if process was in processing and it has "miltiple_run" = 0, we have seen "View State" instead of "Run" button. So, admin could not execute process while other processes with the same process code are in running.
- mapper. mapper template filter. speed up for case when we use too much times the same mapper - multiple entities.
- queue. cron. select from database: small speed-up.
- queue. default interval between re-tries now is 900 sec (15min) instead of 5 sec.
- queue. processing. added support for QueueDependsException.
- queue. digidirect_ai_queue_log table.removed column entity_id. added primary key for "log_id". removed unq idx for log_id + queue_id.
- logs. grid. perfomance fix. do not join to queue_log table if there is no filter by queue_id.
- logs. logging. fixed root case of infinite recursion in some cases of logging usage.
- logs. logging. error handling. log full error text instead of stacktrace only ($e->__toString() instead of $e->getTraceAsString())

### VERSION 1.15.2
- queue. added setting `Generate Run On Error Every (for Pending Depends)`. for queue depends default interval is 900s, for other cases default interval from now is 300s.
- queue. added setting `Number of Runs On Error (Per Integration Process)`. it will override setting `Number of Runs On Error` if specified for process.
- queue. added method getActiveQueueItemIdByProcessCodeAndProcessData.

### VERSION 1.15.3
- Bugfix. Product Import. Label is not imported for the image with 'Alternative' role

### VERSION 1.15.4
- Bugfix. Compatibility with php7.2 fixes.
- Bugfix. Product Import. Fixed error for 2.2.0 magento - used file that does not exist in previous release bugfix.
- Bugfix. Product Import. Categories upsert fix.
- Bugfix. Product Import. Save products fix.

### VERSION 1.15.5
- Bugfix. Email can not be sent with attached Log-file.
- NOTE: required digidirect/utilities release/1.20.1+

### VERSION 1.15.6
- Bugfix. Product Import. Undefined index error. Images/Video import problem. Resolved for 2.2 & 2.3 versions. Video is working.

### VERSION 1.15.7
- Refactoring. Preferences/Model/Import/Product/CategoryProcessor: some private methods declared as protected to override in add-on.
- Bugfix. Product import. Product attributes are not set while using Magento Import functionality
- Bugfix. Product import. Row "XXX" skipped. Error appear: Notice: Undefined index: rowNum

### VERSION 1.15.8
- Bugfix. Product import overwrites URL key of the product to empty value.
- class Digidirect\AI\Model\Lib\Import\Product\Entity marked as deprecated: use Digidirect\AI\Model\Lib\Entity\Import\Product\Product