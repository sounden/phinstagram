# Usage:
# docker build -t phinstagram .
# docker run -d -p 8080:80 phinstagram
# open http://localhost:8080/
#
# based on gist https://gist.github.com/yujiod/31af35417bffb80a08df

FROM php
ADD . /tmp

CMD ["php", "-S", "0.0.0.0:80", "-t", "/tmp"]
