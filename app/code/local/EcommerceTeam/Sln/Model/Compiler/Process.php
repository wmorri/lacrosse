<?php
class EcommerceTeam_Sln_Model_Compiler_Process extends Mage_Compiler_Model_Process
{
	 protected function _getClassesSourceCode($classes, $scope)
    {
        $sortedClasses = array();
        foreach ($classes as $className) {
            $implements = array_reverse(class_implements($className));
            foreach ($implements as $class) {
                if (!in_array($class, $sortedClasses) && !in_array($class, $this->_processedClasses) && strstr($class, '_')) {
                    $sortedClasses[] = $class;
                    if ($scope == 'default') {
                        $this->_processedClasses[] = $class;
                    }
                }
            }
            $extends    = array_reverse(class_parents($className));
            foreach ($extends as $class) {
                if (!in_array($class, $sortedClasses) && !in_array($class, $this->_processedClasses) && strstr($class, '_')) {
                    $sortedClasses[] = $class;
                    if ($scope == 'default') {
                        $this->_processedClasses[] = $class;
                    }
                }
            }
            if (!in_array($className, $sortedClasses) && !in_array($className, $this->_processedClasses)) {
                $sortedClasses[] = $className;
                    if ($scope == 'default') {
                        $this->_processedClasses[] = $className;
                    }
            }
        }

        $classesSource = "<?php\n";
        foreach ($sortedClasses as $className) {
            $file = $this->_includeDir.DS.$className.'.php';
            if (!file_exists($file)) {
                continue;
            }
            $content = file_get_contents($file);
            $content = ltrim($content, '<?php');
            $content = rtrim($content, "\n\r\t?>");
            $classesSource.= 
            "\n\rif (!class_exists('".$className."',false) && !interface_exists('".$className."',false)) {\n\r".$content."\n\r}\n\r";
        }
        return $classesSource;
    }
}