CREATE TABLE User (
    UserID INT PRIMARY KEY AUTO_INCREMENT,
    Username VARCHAR(50) UNIQUE NOT NULL,
    PasswordHash VARCHAR(64) NOT NULL,
    Email VARCHAR(50) UNIQUE NOT NULL
);

CREATE TABLE Artist (
    ArtistID INT PRIMARY KEY AUTO_INCREMENT,
    ArtistName VARCHAR(100) NOT NULL
);

CREATE TABLE Album (
    AlbumID INT PRIMARY KEY AUTO_INCREMENT,
    AlbumName VARCHAR(200) NOT NULL,
    GenreID INT,
    AverageRating DECIMAL(3, 2),
    ReleaseDate DATE,
    ArtistID INT,
    CONSTRAINT FK_Artist_Album FOREIGN KEY (ArtistID) REFERENCES Artist(ArtistID),
    CONSTRAINT FK_Genre_Album FOREIGN KEY (GenreID) REFERENCES Genres(GenreID)
);

CREATE TABLE Track (
    TrackID INT PRIMARY KEY AUTO_INCREMENT,
    TrackName VARCHAR(200) NOT NULL,
    AlbumID INT,
    CONSTRAINT FK_Album_Track FOREIGN KEY (AlbumID) REFERENCES Album(AlbumID)
);

CREATE TABLE Rating (
    RatingID INT PRIMARY KEY AUTO_INCREMENT,
    Rating DECIMAL(2, 1) NOT NULL,
    Review TEXT,
    UserID INT,
    AlbumID INT,
    CONSTRAINT FK_User_Rating FOREIGN KEY (UserID) REFERENCES User(UserID),
    CONSTRAINT FK_Album_Rating FOREIGN KEY (AlbumID) REFERENCES Album(AlbumID)
);

CREATE TABLE Genres (
    GenreID INT PRIMARY KEY AUTO_INCREMENT,
    GenreName VARCHAR(50) NOT NULL
);

CREATE TABLE UserSettings (
    UserID INT PRIMARY KEY,
    Biography TEXT,
    FavoriteArtists TEXT,
    FavoriteGenres TEXT,
    FavoritePlaylist TEXT,
    LastFmUsername VARCHAR(50),
    CONSTRAINT FK_UserSettings_User FOREIGN KEY (UserID) REFERENCES User(UserID)
);