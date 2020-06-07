<?php

class PHPImap {

  // imap server connection
  public $conn;

  // inbox storage and inbox message count
  private $inbox;
  private $msg_cnt;

  // email login credentials
  public $server;
  public $user;
  public $pass;
  public $port; // adjust according to server settings


  // close the server connection
  function close() {
    $this->inbox = array();
    $this->msg_cnt = 0;

    imap_close($this->conn);
  }

  // open the server connection
  // the imap_open function parameters will need to be changed for the particular server
  // these are laid out to connect to a Dreamhost IMAP server
  function connect() {
    $hostname = '{' . $this->server . ':' . $this->port . '/imap/ssl}INBOX';
    return $this->conn = imap_open($hostname, $this->user, $this->pass) or die('Cannot connect to Server: ' . imap_last_error());
  }

  // move the message to a new folder
  function move($msg_index, $folder='INBOX.Processed') {
    // move on server
    imap_mail_move($this->conn, $msg_index, $folder);
    imap_expunge($this->conn);

    // re-read the inbox
    $this->inbox();
  }

  // get a specific message (1 = first email, 2 = second email, etc.)
  function get($msg_index=NULL) {
    if (count($this->inbox) <= 0) {
      return array();
    }
    elseif ( ! is_null($msg_index) && isset($this->inbox[$msg_index])) {
      return $this->inbox[$msg_index];
    }

    return $this->inbox[0];
  }

  // read the inbox
  function inbox() {
    $this->msg_cnt = imap_num_msg($this->conn);

    $in = array();
    for($i = 1; $i <= $this->msg_cnt; $i++) {
      $in[] = array(
        'index'     => $i,
        'header'    => imap_headerinfo($this->conn, $i),
        'body'      => imap_body($this->conn, $i),
        'structure' => imap_fetchstructure($this->conn, $i)
      );
    }
    return $this->inbox = $in;
  }
  function search($q) {
    $msgnos = imap_search($this->conn, $q, SE_UID);
    $rp = array();
    if(!empty($msgnos)) {
    foreach($msgnos as $msgUID) {
      $msgNo = imap_msgno($this->conn, $msgUID);
      $head = imap_headerinfo($this->conn, $msgNo);
      $body = imap_body($this->conn, $msgNo);
      $rp[] = array(
        //'msgNo'   => $msgNo,
        'head'    => json_decode(json_encode($head), true),
        'id'      => $msgUID,
        'recent'  => $head->Recent,
        'unseen'  => $head->Unseen,
        'from'    => $head->from[0]->mailbox . '@' . $head->from[0]->host,
        'subject' => utf8_decode(imap_utf8($head->subject)),
        'date'    => $head->udate,
        'body'    => $body,
      );
    }
    }
    return $rp;
  }
}

