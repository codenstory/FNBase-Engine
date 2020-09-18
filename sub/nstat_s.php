<?php
            switch($total){
                case '0': 
                    $lb = '<span class="label error">멸망</span> ';
                break;
                case $total > 59:
                    $lb = '<span class="label" style="background:purple">초강대국</span> ';
                break;
                case $total > 55:
                    $lb = '<span class="label normal">강대국</span> ';
                break;
                case $total > 46:
                    $lb = '<span class="label success">지역강국</span> ';
                break;
                case $total > 41:
                    $lb = '<span class="label warning">중견국</span> ';
                break;
                case $total > 0:
                    $lb = '<span class="label error">소국</span> ';
                break;
            }
?>