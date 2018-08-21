<?php

namespace PTXDev\Phan\Plugins;

use Phan\Config;
use Phan\Language\Type;
use Phan\Language\UnionType;
use Phan\PluginV2;
use Phan\PluginV2\SuppressionCapability;

class SuppressPlugin extends PluginV2 implements SuppressionCapability
{
    protected $parameters;

    public function shouldSuppressIssue(
        \Phan\CodeBase $codeBase,
        \Phan\Language\Context $context,
        string $issueType,
        int $lineno,
        array $parameters,
        $suggestion
    ): bool {
        $this->parameters = $parameters;
        $filename = $context->getFile();

        // 不需要报错的文件
        $suppressFiles = array_map(function ($file) {
            return Config::getProjectRootDirectory() . DIRECTORY_SEPARATOR . $file;
        }, []);
        // 插件不报错
        $suppressFiles[] = 'internal';

        if (in_array($filename, $suppressFiles)) {
            return true;
        }

        $suppress = Config::toArray()['suppress'] ?? [];
        $config = $suppress[$issueType] ?? [];

        if (in_array($issueType, [
            'PhanTypeMismatchArgument',
            'PhanTypeMismatchArgumentInternal'
        ])) {
            $method = $this->getMethodName();
            if (isset($config[$method])) {
                $methodConfig = $config[$method];
                if ($this->checkArgIndex($methodConfig['argIndex'] ?? true) &&
                    $this->checkParamTypes($methodConfig['paramTypes'] ?? true)) {
                    return true;
                }
            }
        } elseif (in_array($issueType, [
            'PhanUndeclaredStaticMethod',
            'PhanAccessNonStaticToStatic'
        ])) {
            $method = $this->getStaticMethodName();
            if (in_array($method, $config)) {
                return true;
            }
        }


        // $this->debug($issueType, $filename, $lineno, $suggestion, $parameters);
        return false;
    }

    private function getStaticMethodName()
    {
        $method = $this->parameters[0];
        if (is_object($method)) {
            $method = $method->getName();
        }
        return $method;
    }

    /**
     * 检查参数位置
     * @param int|array|bool $validIndex
     * @return bool
     */
    private function checkArgIndex($validIndex = true)
    {
        if ($validIndex === true) {
            return true;
        }
        $argIndex = $this->parameters[0];
        if (is_array($validIndex)) {
            return in_array($argIndex, $validIndex);
        } else {
            return $argIndex === $validIndex;
        }
    }

    /**
     * 检查参数类型
     * @param mixed $types
     * @return bool
     */
    private function checkParamTypes($types)
    {
        if (!is_array($types)) {
            $types = [$types];
        }

        /**
         * @var UnionType $unionType
         * @var Type[]    $typeSet
         */
        $unionType = $this->parameters[2];
        $typeSet = $unionType->getTypeSet();
        foreach ($typeSet as $set) {
            if (!in_array($set->getName(), $types)) {
                return false;
            }
        }
        return true;
    }

    private function getMethodName()
    {
        return $this->parameters[3];
    }

    private function debug($issueType, $filename, $lineno, $suggestion, $parameters)
    {
        var_dump(func_get_args());
    }

    /**
     * This method is used only by UnusedSuppressionPlugin.
     * It's optional to return lines for issues that were already suppressed.
     * To get the file's current contents, the recommended method is:
     * $file_contents = \Phan\Library\FileCache::getOrReadEntry(Config::projectPath($file_path))->getContents()
     * @param \Phan\CodeBase $code_base
     * @param string         $file_path the file to check for suppressions of
     * @return array<string,array<int,int>> Maps 0 or more issue types to a *list* of lines that this plugin is going to suppress.
     * An empty array can be returned if this is unknown.
     */
    public function getIssueSuppressionList(
        \Phan\CodeBase $code_base,
        string $file_path
    ): array {
        return [];
    }
}

return new SuppressPlugin();
