#! /usr/bin/php
<?

  require_once( __DIR__."/vendor/autoload.php" );
  require_once( __DIR__."/.dropbox-credentials" );

  use Kunnu\Dropbox\Dropbox;
  use Kunnu\Dropbox\DropboxApp;
  use Kunnu\Dropbox\DropboxFile;
  use Rimksky\Util\Config;

  try{
    $config= Config::getInstance()->config;
    $app = new DropboxApp( "", "", $config->token );
    $dropbox = new Dropbox( $app );

    $items = $dropbox->listFolder( $config->srcDir )->getItems();
    $sort = function( $a, $b ){ 
      return ( $a->client_modified > $b->client_modified );
    };
    foreach( $items->sort( $sort ) as $item ){
      if( $item->{ ".tag" }."" !== "file" ){
        continue 1;
      }
      if( !preg_match( "/\.(jpg|jpeg|mov|mp4)$/i", $item->name ) ){
        continue 1;
      }

      $src = $item->getPathLower();
      $dst = "{$config->dstDir}/".$item->getName();

      echo "movinig file:\n src:{$src}\n dst:{$dst}\n";
      try{
        $dropbox->move( $src, $dst );
      }
      catch( Kunnu\Dropbox\Exceptions\DropboxClientException $e ){
        $err = json_decode( $e->getMessage() );
        if( $err->error->{ ".tag" }."" === "to" ){
          echo "waring: ignore moving {$dst}\n";
        }
        else{
          throw $e;
        }
      }
    }
  }
  catch( Exception $e ){
    throw $e;
  }

?>
