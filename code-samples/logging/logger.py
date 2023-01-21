import logging
import time
from logging.handlers import TimedRotatingFileHandler

#----------------------------------------------------------------------
class CargoFlLogger:
    '''
    CargoFL Specific Log File, which rotates on a Daily basis (or) at a default 
    log_name = Name of the module, for e.g. CargoFLCore
    logging_level = Logging level. Use among - debug, info, error
    logfile_path = Path of the Log file including file name
    max_filesize_to_rotate_in_bytes = Max file size after which file will rotate
    follow_utc_time = True to follow UTC time, or False to follow Server time
    '''
    def __init__(self, log_name, logging_level, logfile_path, max_filesize_to_rotate_in_bytes, follow_utc_time):
        self.LOGGING_LEVEL={
            "debug" : logging.DEBUG,
            "info" : logging.INFO,
            "error" : logging.ERROR
        }

        loglevelx = self.LOGGING_LEVEL['info']        

        self.loglevel = self.LOGGING_LEVEL[logging_level.lower()]
        self.logfile = logfile_path 
        self.maxFileSize = max_filesize_to_rotate_in_bytes # TBC: add lamda to set default to 10 Mb if not propvided
        self.followServerTime = follow_utc_time # TBC: add lamda to set default to False - i.e. local server time

        # Next create logger
        self.logger = logging.getLogger(log_name)

        # TBC: add lamda to throw error if not acceptable + default to info
        self.logger.setLevel(self.loglevel)

        '''
        --- When - Configuration ---
        'S' - Seconds 
        'M'- Minutes
        'H' - Hours
        'D' -Days
        'W0'-'W6' - Weekday (0=Monday)
        'midnight' - Roll over at midnight, if atTime not specified, else at time atTime
        '''
        handler = TimedRotatingFileHandler(self.logfile,
                                        when="midnight", # Hardcoded to Midnight for CargoFL implementation
                                        interval=1,     
                                        backupCount=10 # Hard coded to 10 files all the time
                                        #size=self.maxFileSize
                                        # utc = self.followServerTime)
                                        )
        self.logger.addHandler(handler)



    # Info Function to return current logging level
    def get_current_log_level(self,logging_level):
        print ("... Logging level requested = " + logging_level)
        return self.loglevel 


    def debug(self, log_msg):
        print ("...Logging debug ")
        self.logger.debug(log_msg)

    def info(self, log_msg):
        print ("...Logging Info ")
        self.logger.info(log_msg)

#----------------------------------------------------------------------

# Set defaults
print ("... Starting  ")

# Get these values from properties file or from command line Args
log = CargoFlLogger(
    log_name="CargoFLCore", 
    logging_level="debug",
    logfile_path="./cargofl-core.log", 
    max_filesize_to_rotate_in_bytes=10000000,
    follow_utc_time=False
)

log.info("DK Test ")

print ("... End  ")
