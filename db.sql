CREATE TABLE ImageTable (
  ImageId int(100) NOT NULL AUTO_INCREMENT,
  ImageName varchar(50) NOT NULL,
  ImageFile longblob NOT NULL,
  KEY ImageId (ImageId)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;