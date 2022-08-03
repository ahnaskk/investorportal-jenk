<?php
//shell_exec ('cd ../..; wget https://www.dropbox.com/s/soxdwa9839wwwml/storage.zip');
shell_exec ('cd ../..; rm -r storage; unzip storage.zip; composer install');
echo "Done";
