<?php

namespace Config\Error;

use Config\ConfigException;

class ParentNotFoundException extends  \BadMethodCallException implements
    ConfigException
{
}
