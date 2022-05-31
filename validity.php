<?php
// `sudo dnf install php-pecl-mailparse` is needed to run this...this is fedora...use yum if Centos or apt under Debian/Ubuntu
// run as root in same directory as email tar file...reccomend to not run this in web root...ie `php validity.php`
//create database validity;
//use validity;
//grant all privileges on validity.* to 'validity'@'localhost' identified by 'validity';
//flush privileges;
//create table email_data (id int(5) not null auto_increment, `to` varchar(200), `from` varchar(200), `subject` varchar(500), `message_id` varchar(150), `date` varchar(100), primary key(`id`));

$conn = mysqli_connect('localhost', 'validity', 'validity', 'validity');

$tar      = __dir__ . '/sampleEmails.tar.gz'; //tar file name
$file_dir = __dir__ . '/extracted';           //directory for files
if(file_exists($file_dir))
    shell_exec("rm $file_dir -R -f");

mkdir($file_dir);

$phar     = new PharData($tar);
$phar->extractTo($file_dir);
shell_exec("mv $file_dir/smallset/* $file_dir/");//move files out of sub directory
shell_exec("rm $file_dir/smallset/ -r -f");      //drop subdirectory to keep things clean

$files = scandir($file_dir); //get files into array
    foreach($files as $file)
        if(preg_match('/msg$/', $file_dir . '/' . $file)) { //make sure we aren't getting the folder itself and such we just want the ms files here
           $msg = mailparse_msg_get_part_data ( mailparse_msg_parse_file($file_dir . '/' . $file) ); //get message into array

           mysqli_query($conn, vsprintf("insert into email_data (`id`, `to`, `from`, `message_id`, `date`, `subject`)
                                        values(null, '%s', '%s', '%s', '%s', '%s')", [
                                                                                        mysqli_real_escape_string($conn, $msg['headers']['to']),
                                                                                        mysqli_real_escape_string($conn, $msg['headers']['from']),
                                                                                        mysqli_real_escape_string($conn, $msg['headers']['message-id']),
                                                                                        mysqli_real_escape_string($conn, $msg['headers']['date']),
                                                                                        mysqli_real_escape_string($conn, $msg['headers']['subject'])
                                                                                    ]));
           echo mysqli_error($conn);
        }

