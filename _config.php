<?php


/**
 * developed by www.sunnysideup.co.nz
 * author: Nicolaas - modules [at] sunnysideup.co.nz
**/


//copy the lines between the START AND END line to your /mysite/_config.php file and choose the right settings
//===================---------------- START image_placeholder_replacer MODULE ----------------===================
//MUST HAVE
//Object::add_extension('SiteConfig', 'ImagePlaceHolderReplacer');
//CAN HAVE
//ImagePlaceHolderReplacer::set_folder_name("SampleImages");
//ImagePlaceHolderReplacer::add_image_to_replace($className = "Page", $fieldName = "SummaryImage", $notes = "260px wide, 150px high", "$copyFrom = themes/mytheme/images/sampleimages/mySampleSummaryImage.png");

//===================---------------- END image_placeholder_replacer MODULE ----------------===================


//to run ...

//http://dev.mysite.com/dev/build/?flush=1 - puts placeholder for ALLL image fields listed if no image is listed in a particular record yet
//http://dev.mysite.com/dev/build/?flush=1&forceplaceholder=1 - puts placeholder for ALLL image fields listed even if there is another image there already
//http://dev.mysite.com/dev/build/?flush=1&removeplaceholderimages=1 - removes all placeholder images
