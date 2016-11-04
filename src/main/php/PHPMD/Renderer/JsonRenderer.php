<?php
/**
 * This file is part of PHP Mess Detector.
 *
 * Copyright (c) 2008-2012, Manuel Pichler <mapi@phpmd.org>.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *   * Redistributions of source code must retain the above copyright
 *     notice, this list of conditions and the following disclaimer.
 *
 *   * Redistributions in binary form must reproduce the above copyright
 *     notice, this list of conditions and the following disclaimer in
 *     the documentation and/or other materials provided with the
 *     distribution.
 *
 *   * Neither the name of Manuel Pichler nor the names of his
 *     contributors may be used to endorse or promote products derived
 *     from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @author    Manuel Pichler <mapi@phpmd.org>
 * @copyright 2008-2014 Manuel Pichler. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php BSD License
 */

namespace PHPMD\Renderer;

use PHPMD\AbstractRenderer;
use PHPMD\PHPMD;
use PHPMD\Report;
use PHPMD\RuleViolation;

/**
 * This class will render a JSON report.
 *
 * @author    Manuel Pichler <mapi@phpmd.org>
 * @copyright 2008-2014 Manuel Pichler. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php BSD License
 */
class JsonRenderer extends AbstractRenderer
{
    /**
     * Create report data and add renderer meta properties
     *
     * return array
     */
    private function initReportData()
    {
        $data = array(
            'version' => PHPMD::VERSION,
            'package' => 'phpmd',
            'timestamp' => date('c'),
        );

        return $data;
    }

    /**
     * {@inheritDoc}
     */
    public function renderReport(Report $report)
    {
        $data = $this->initReportData();
        $filesList = array();
        /** @var RuleViolation $violation */
        foreach ($report->getRuleViolations() as $violation) {
            $fileName = $violation->getFileName();
            $rule = $violation->getRule();
            $filesList[$fileName]['file'] = $fileName;
            $filesList[$fileName]['violations'][] = array(
                'beginLine' => $violation->getBeginLine(),
                'endLine' => $violation->getEndLine(),
                'package' => $violation->getNamespaceName(),
                'function' => $violation->getFunctionName(),
                'class' => $violation->getClassName(),
                'method' => $violation->getMethodName(),
                'description' => $violation->getDescription(),
                'rule' => $rule->getName(),
                'ruleSet' => $rule->getRuleSetName(),
                'externalInfoUrl' => $rule->getExternalInfoUrl(),
                'priority' => $rule->getPriority(),
            );
        }
        $errorsList = array();
        foreach ($report->getErrors() as $error) {
            $errorsList[] = array(
                'fileName' => $error->getFile(),
                'message' => $error->getMessage(),
            );
        }
        $data['files'] = array_values($filesList);
        $data['errors'] = $errorsList;
        $json = $this->encodeReport($data);
        $writer = $this->getWriter();
        $writer->write($json . PHP_EOL);
    }

    /**
     * Encode report data to the JSON representation string
     *
     * @param $data array The report data
     *
     * @return string
     */
    private function encodeReport($data)
    {
        $encodeOptions = JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP;
        // JSON_PRETTY_PRINT Available since PHP 5.4.0.
        if (defined('JSON_PRETTY_PRINT')) {
            $encodeOptions |= JSON_PRETTY_PRINT;
        }

        return json_encode($data, $encodeOptions);
    }
}
