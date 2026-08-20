<style>
  .starter-template {
    text-align: center;
  }


  table>tr>td {
    /* cell-padding: 5px !important; */
  }

  @media print {
    #cetak {
      display: none;
    }
  }

  .garis {
    border: 1px solid
  }

  .table2 {
    font-size: 5px;
    text-align: center
  }

  .result {
    border-collapse: collapse;
  }

  .result td {
    /* border: 1px solid black; */
    text-align: center;

  }

  @page {
    size: 794px 1248px;
    margin: 2em 3em 3em 3em;
  }

  @font-face {
    font-family: "source_sans_proregular";
    src: local("Source Sans Pro"), url("fonts/sourcesans/sourcesanspro-regular-webfont.ttf") format("truetype");
    font-weight: normal;
    font-style: normal;
    /* font-size: 11px; */
  }

  body {
    font-family: Arial, Calibri, Candara, Segoe, Segoe UI, Optima, Arial, sans-serif;
    font-size: 13px;
    text-align: justify;
    text-justify: inter-word;
  }

  .page_break {
    page-break-before: always;
  }

  .clearfix {
    overflow: auto;
  }

  .clearfix::after {
    content: "";
    clear: both;
    display: table;
  }

  .nospace {
    margin: 0;
    padding: 0;
  }
</style>