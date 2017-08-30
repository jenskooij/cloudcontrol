<!DOCTYPE html>
<html>
  <head>
    <title><?= $error['message'] ?></title>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <!--<style type="text/css">
          html, body, div, span, applet, object, iframe,
          h1, h2, h3, h4, h5, h6, p, blockquote, pre,
          a, abbr, acronym, address, big, cite, code,
          del, dfn, em, font, img, ins, kbd, q, s, samp,
          small, strike, strong, sub, sup, tt, var,
          b, u, i, center,
          dl, dt, dd, ol, ul, li,
          fieldset, form, label, legend,
          table, caption, tbody, tfoot, thead, tr, th, td {margin:0;padding:0;border:0;outline:0;font-size:100%;vertical-align:baseline;background:transparent;}
          body {line-height:1;font-family:Trebuchet MS, sans-serif;font-size:12px;padding:15px;}
          ol, ul {list-style:none;}
          blockquote, q {quotes:none;}
          blockquote:before, blockquote:after, q:before, q:after {content:'';content:none;}
          :focus {outline:0;}
          ins {text-decoration:none;}
          del {text-decoration:line-through;}
          table{width:100%;min-width:800px;border-collapse:collapse;border-spacing:0;border:1px solid #f0f0f0;}
          th, td{text-align:left;padding:3px;}
          tr:hover td{background-color:#f4f4eb;}
          th{border-bottom:1px solid #e5e5e5;background-color:#f6f6f6;border-right:1px solid #ccc;}
          td{border-bottom:1px dotted #ccc;border-right:1px dotted #ccc;}
          td:first-child, th:first-child{width:40px;text-align:right;}
          pre{padding:0px;margin:0px;font-family:Consolas, Courier New, Courier;}
          h1{font-size:14pt;margin-top:0;color:#c00;}
          h3{font-size:12pt;margin-top:10px;}
      </style>
      -->
  </head>
  <body>
    <h1 class="text-danger"><?= $error['message'] ?> in <?= $error['file'] ?>:<?= $error['line'] ?></h1>
    <table class="table table-bordered table-striped">
      <tr>
        <th>File</th>
        <td><samp><?= $error['file'] ?><samp></td>
      </tr>
      <tr>
        <th>Line</th>
        <td><?= $error['line'] ?></td>
      </tr>
      <tr>
        <th>Code</th>
        <td><?= $error['code'] ?></td>
      </tr>
    </table>
      <? if (isset($error['lines'])) : ?>
    <h2>File</h2>
    <pre>
<? foreach ($error['lines'] as $nr => $line) : ?>
  <code data-line="<?= $nr ?>. " class="<?= $error['line'] == $nr ? ' active' : '' ?>"><?= htmlentities($line) ?></code>
<? endforeach ?>
        <? endif ?>
</pre>
    <h2>Trace</h2>
    <pre>
<? print_r($error['trace']) ?>
</pre>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/9.0.0/highlight.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/9.0.0/styles/default.min.css"/>
    <script>hljs.initHighlightingOnLoad();</script>
    <style>code.hljs {
        margin: -0.4em 0;
      }

      code.active {
        background-color: #e0e0e0;
      }

      code:before {
        content: attr(data-line);
      }

      code.active:before {
        color: #c00;
        font-weight: bold;
      }</style>
  </body>
</html>