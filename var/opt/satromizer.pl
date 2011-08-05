#!/usr/bin/perl -w

#=============================
# jon.satrom (2006)
#
# http://jonsatrom.com/satromizer
#
# Creative Commons: Attribution-Share Alike 3.0
# http://creativecommons.org/licenses/by-sa/3.0/us/
#
# special thnx to bensyverson
#
#
#=============================


use strict;

# ======== command line options =========
my $dirname 		= $ARGV[0];
my $destdir 		= $ARGV[1];
my $headersize 		= $ARGV[2];
my $numoperations 	= $ARGV[3];
my $minchunk 		= $ARGV[4];
my $maxchunk 		= $ARGV[5];
my $mindist 		= $ARGV[6];
my $maxdist 		= $ARGV[7];

($minchunk, $maxchunk) = ($maxchunk, $minchunk) if ($minchunk > $maxchunk);
($mindist, $maxdist) = ($maxdist, $mindist) if ($mindist > $maxdist);

print 		"    dirname: $dirname \n"
		.	"destination: $destdir \n"
		.	" headersize: $headersize \n"
		.	" num of ops: $numoperations \n"
		.	"  min chunk: $minchunk \n"
		.	"  max chunk: $maxchunk \n"
		.	"   min dist: $mindist \n"
		.	"   max dist: $maxdist \n";


#=============================
# Figure out the smallest file
#=============================

my $smallest = 0;
my $imagecount = 0;
opendir(DIR, $dirname) or die "can't open dir $dirname: $!";
while (defined(my $file = readdir(DIR))) {										# iterate over all the files in $dirname
	unless ($file eq '.' or $file eq '..' or $file =~ /^\..*$/) {				# skip the "." and ".." entries, and any file starting with "."	
		my $size = -s "$dirname/$file";
		$smallest = $size if ($smallest == 0 || $smallest > $size); 			# set $smallest to the size of the current file if if this is the first or smallest file we've seen
		$imagecount++;
	}
}
print "Smallest filesize: $smallest \n";

my $lastpos = $smallest - $maxchunk;
print "Last available chunk position (smallest file - maxchunk): $lastpos \n";
die "Sorry; header size is larger than the last available chunk position; " .
		"try a smaller header or smaller chunk. \n" if ($headersize > $lastpos) ;




#==============================
# Build up a list of operations
#==============================

my @operations;

for(my $i=0; $i < $numoperations; $i++) {
	# determine chunk position
	my $chunkposition;
	my $chunksize;
	my $movdist;
	while (1) {
		$chunkposition 	= int( rand( $lastpos - $headersize + 1) ) + $headersize;		# pick a chunk position that's between the end of the header and the last available spot
		$chunksize 		= int( rand( $maxchunk - $minchunk + 1) ) + $minchunk;			# pick a random chunk size between our range
		$movdist 		= int( rand( $maxdist  - $mindist + 1) ) + $mindist;			# pick a random move distance between our range
		
		last unless (($chunkposition + $movdist) < $headersize or ($chunkposition + $chunksize + $movdist) > $smallest);	# make sure we don't go over our range
	}
	
	$operations[$i] = {															# populate our array entry with this hash
		'chunkposition' => $chunkposition, 
		'chunksize'		=> $chunksize,
		'movdist'		=> $movdist,
	};
	
	if ($numoperations < 25) {
		print "* Operation $i: Go to $operations[$i]{'chunkposition'} in file, " .
				"select $operations[$i]{'chunksize'} bytes, and move it " .
				"$operations[$i]{'movdist'} within the file. \n";
	}
}

#=====================================
# Perform the operations on every file
#=====================================

my $currentimage = 0;
my $cursorpos = 0;
my $direction = 1;
print "############# Processing $imagecount images...\n";
opendir(DIR, $dirname) or die "can't open dir $dirname: $!";
while (defined(my $file = readdir(DIR))) {										# iterate over all the files in $dirname
	unless ($file eq '.' or $file eq '..' or $file =~ /^\..*$/) {				# skip the "." and ".." entries, and any file starting with "."
		# animate the cursor to provide a little feedback as we process images...
		my $percent = int($currentimage / $imagecount * 100);
		print "$file (" . ($percent < 10 ? ' ' : '') . $percent . '%) ' . ' ' x $cursorpos;
		if ($direction == 1) {
			print '\\' . "\n";
			$direction = -1 if ($cursorpos > 30);
		} else {
			print '/' . "\n";
			$direction = 1 if ($cursorpos < 1);
		}
		$cursorpos += $direction;
		$currentimage++;
		# end cursor animation
		
		local( *FILE);															# slurp in the file, part 1
		open(FILE, "< :raw", "$dirname/$file" ) or die "whoops: $!\n";			# open the file
		my $data = do { local( $/ ) ; <FILE> } ;								# slurp in the file, part 2
		close FILE;																# close the file
		if ($data) {															# if we have data
			foreach(@operations) {																	# then for each operation
				my $chunk 		= substr($data, $$_{'chunkposition'}, $$_{'chunksize'});			# select our chunk
				substr($data, $$_{'chunkposition'}, $$_{'chunksize'}) = '';							# delete the chunk
				$$_{'movdist'} -= $$_{'chunksize'} if ($$_{'movdist'} > 0);							# if we're moving the chunk down, compensate for the fact that we just deleted the chunk
				my $firsthalf 	= substr($data, 0, $$_{'chunkposition'} + $$_{'movdist'});			# grab everything from the beginning up to where we're moving our chunk
				my $lasthalf 	= substr($data, $$_{'chunkposition'} + $$_{'movdist'});				# grab everything from where we're moving our chunk on
				$data = $firsthalf . $chunk . $lasthalf if ($chunk and $firsthalf and $lasthalf);	# stick it all together in the right order
			}
			open (OUTPUT, "> :raw", "$destdir/$file") or die "could not open $destdir/$file: $! \n";
			print OUTPUT $data;
			close OUTPUT;
		} else {
			print " # error";
		}
	}
}
closedir(DIR);
print "############# Finished. \n";